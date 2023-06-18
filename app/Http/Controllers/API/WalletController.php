<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\NewTransactionRequest;
use App\Http\Requests\Wallet\StoreWalletTransactionRequest;
use App\Models\CoinWalletTransaction;
use App\Models\CoinWalletTransactionReason;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionReason;
use App\Payment\Gateways\Mellat\Mellat;
use App\Payment\Gateways\Zarinpal\ZarinPal;
use App\Traits\FileHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class WalletController extends BaseController
{
    use FileHandler;

    /**
     * Show wallet detail
     *
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *     path="/api/v1/user/wallet",
     *     summary="Get user's wallet",
     *     description="Returns the current user's wallet",
     *     operationId="getWallet",
     *     tags={"User"},
     *     security={ {"sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        return $this->sendResponse($wallet, Lang::get('http-statuses.200'));
    }

    /**
     * Insert New transaction for wallet
     *
     * @param StoreWalletTransactionRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeTransaction(StoreWalletTransactionRequest $request)
    {
        $validated = $request->validated();
        if (!empty($validated['user_id'])) {
            $user = User::find($validated['user_id']);
            $changer = Auth::user();
        } else {
            $user = Auth::user();
            $changer = $user;
        }
        $wallet = $user->wallet;

        $reason_id = WalletTransactionReason::whereCode($validated['reason_code'])->first()->id;

        $newAmount = $wallet->amount;
        switch ($validated['action']) {
            case 'increase':
                $newAmount = $wallet->amount + $validated['amount'];
                break;
            case 'decrease':
                $newAmount = $wallet->amount - $validated['amount'];
                if ($newAmount < 0) {
                    return $this->sendError('مقدار مبلغ وارد شده بیشتر از موجودی کیف پول میباشد', [], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                break;
        }

        $wallet->update(['amount' => $newAmount]);
        $wallet = $wallet->fresh();

        unset($validated['user_id']);
        unset($validated['reason_code']);
        unset($validated['image']);

        $validated['user_id'] = $user->id;
        $validated['final_amount'] = $newAmount;
        $validated['reason_id'] = $reason_id;
        $validated['changer_id'] = $changer->id;

        $transaction = WalletTransaction::query()->create($validated);

        if (!empty($request->image)) {
            $path = $this->uploadNewTransactionImage($request->image, $transaction);
            $transaction->update(['image_path' => $path]);
        }

        return $this->sendResponse(compact('wallet'), 'تراکنش با موفقیت انجام شد');
    }

    /**
     * Buy coin with wallet
     *
     * @param Request $request
     * @return Response
     */
    public function buyCoin(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric']
        ]);

        $coinPrice = 100; // each coin price

        $user = Auth::user();
        $wallet = $user->wallet;
        $coinWallet = $user->coinWallet;

        if ($validated['amount'] > $wallet->amount) {
            return $this->sendError('مقدار مبلغ وارد شده بیشتر از موجودی کیف پول میباشد', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($validated['amount'] < $coinPrice) {
            return $this->sendError('مبلغ وارد شده کمتر از هزینه یک سکه است، هزینه هر سکه' . $coinPrice . ' تومان است', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // convert input amount to coins
        $coins = round($validated['amount'] / $coinPrice);
        $newCoins = $coinWallet->coins + $coins;
        $newAmount = $wallet->amount - $validated['amount'];

        $coinWallet->update(['coins' => $newCoins]);
        $wallet->update(['amount' => $newAmount]);

        $coinWallet = $coinWallet->fresh();
        $wallet = $wallet->fresh();

        // insert wallet transaction for buy coin
        $walletTransaction = WalletTransaction::query()->create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'action' => 'decrease',
            'final_amount' => $newAmount,
            'reason_id' => WalletTransactionReason::query()->whereCode(21)->first()->id,
            'changer_id' => $user->id
        ]);

        // insert coin wallet transaction for buy coin
        $coinWalletTransaction = CoinWalletTransaction::query()->create([
            'user_id' => $user->id,
            'coins' => $coins,
            'action' => 'increase',
            'final_coins' => $newCoins,
            'reason_id' => CoinWalletTransactionReason::query()->whereCode(11)->first()->id,
            'changer_id' => $user->id,
            'wallet_transaction_id' => $walletTransaction->id
        ]);

        // add coin wallet transaction id to wallet transaction
        $walletTransaction->update([
            'coin_wallet_transaction_id' => $coinWalletTransaction->id
        ]);

        return $this->sendResponse(compact('wallet', 'coinWallet'), 'تراکنش با موفقیت انجام شد');
    }

    /**
     * Increase wallet with online gateway
     *
     * @param Request $request
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function increaseWalletOnline(Request $request)
    {
        $validated = $request->validate([
            'gateway' => ['required', 'in:' . implode(',', config('payment.active_gateways'))],
            'amount' => ['required', 'numeric']
        ]);

        $user = Auth::user();

        $gateway = $this->gatewayFactory($validated['gateway']);

        $pay = $gateway->setAmount($validated['amount'])
            ->setCallbackURL(route('wallet::increase.verify-payment'))
            ->setUserID($user->id)
            ->setMobile($user->mobile)
            ->setEmail($user->email)
            ->setDescription('افزایش موجودی کیف پول')
            ->pay();


        if (!$pay->isOk()) {
            return $this->sendError($pay->getErrorMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        switch ($validated['gateway']) {
            case 'zarinpal':
                $transaction_number = $pay->getAuthorityCode();
                break;
            case 'mellat':
                $transaction_number = $pay->getReferenceID();
                break;
        }

        Transaction::query()->create([
            'user_id' => $user->id,
            'gateway' => $validated['gateway'],
            'transaction_number' => $transaction_number,
            'amount' => $validated['amount'],
            'status' => 'unpaid'
        ]);

        return $this->sendResponse([
            'payment_url' => $pay->getPayURL()
        ]);
    }


    /**
     * Verify increase wallet payment
     *
     * @param Request $request
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verifyIncreaseWalletPayment(Request $request)
    {
        if (!empty($request->Authority)) {
            // zarinpal gateway
            return $this->verifyIncreaseWalletPaymentHanlder('zarinpal', $request->Authority);
        } else if (!is_null($request->ResCode)) {
            // mellat gateway
            $resCode = $request->ResCode;
            $refID = $request->RefId;
            $saleRefID = $request->SaleReferenceId;

            if ($resCode != 0) {
                return 'پرداخت انجام نشد!';
            }

            return $this->verifyIncreaseWalletPaymentHanlder('mellat', $refID, ['sale_ref_id' => $saleRefID]);
        }

        return 'فرایند قابل انجام نیست';
    }

    /**
     * Verify increase wallet payment handler
     *
     * @param $gatewayName
     * @param $verifyCode
     * @param array $attributes
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function verifyIncreaseWalletPaymentHanlder($gatewayName, $verifyCode, array $attributes = [])
    {
        $transaction = Transaction::where('transaction_number', $verifyCode)->first();
        if (!$transaction) {
            return 'تراکنش یافت نشد';
        }

        if ($transaction->status == 'paid') {
            return 'تراکنش قبلا پرداخت شده';
        }

        $gateway = $this->gatewayFactory($gatewayName);

        switch ($gatewayName) {
            case 'zarinpal':
                $verify = $this->zarinpalVerifyIncreaseWalletPayment($gateway, $verifyCode, $transaction->amount);
                break;
            case 'mellat':
                $verify = $this->mellatVerifyIncreaseWalletPayment($gateway, $attributes['sale_ref_id']);
        }

        if (!$verify->isOk()) {
            return 'تایید تراکنش با خطا مواجه شد';
        }

        $user = $transaction->user;
        $wallet = $user->wallet;

        $transaction->update([
            'status' => 'paid',
            'transaction_at' => now()
        ]);

        $newAmount = $wallet->amount + $transaction->amount;
        $wallet->update([
            'amount' => $newAmount
        ]);

        // insert wallet transaction for increase wallet
        $walletTransaction = WalletTransaction::query()->create([
            'user_id' => $user->id,
            'amount' => $transaction->amount,
            'action' => 'increase',
            'final_amount' => $newAmount,
            'reason_id' => WalletTransactionReason::query()->whereCode(11)->first()->id,
            'transaction_id' => $transaction->id,
            'changer_id' => $user->id
        ]);

        return 'پرداخت با شماره پیگیری ' . $verify->getReferenceID() . ' با موفقیت انجام شد';
    }

    /**
     * Verify payment with zarinpal
     *
     * @param ZarinPal $instance
     * @param $authority
     * @param $amount
     * @return ZarinPal
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function zarinpalVerifyIncreaseWalletPayment(ZarinPal $instance, $authority, $amount)
    {
        $verify = $instance->setAuthorityCode($authority)->setAmount($amount)->verify();
        return $verify;
    }

    /**
     * Verify payment with mellat
     *
     * @param Mellat $instance
     * @param $saleRefId
     * @return Mellat|mixed
     */
    public function mellatVerifyIncreaseWalletPayment(Mellat $instance, $saleRefId)
    {
        $verify = $instance->verify($saleRefId);
        return $verify;
    }


    /**
     * Call gateway instance
     *
     * @param $gateway
     * @return Mellat|ZarinPal
     */
    protected function gatewayFactory($gateway)
    {
        switch ($gateway) {
            case 'zarinpal':
                $gatewayInstance = new ZarinPal();
                break;
            case 'mellat':
                $gatewayInstance = new Mellat();
                break;
        }

        return $gatewayInstance;
    }

    /**
     * Upload new transaction image
     *
     * @param $file
     * @param $transaction
     * @return string
     */
    protected function uploadNewTransactionImage($file, $transaction)
    {
        $imagePath = 'uploads/transaction/wallet/' . $transaction->id . '/';
        $fileName = $this->uploadFile($file, $imagePath);

        return $imagePath . $fileName;
    }
}
