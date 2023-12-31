<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoinWallet\StoreCoinWalletTransactionRequest;
use App\Http\Requests\CoinWallet\StoreTravelTransactionRequest;
use App\Http\Requests\Wallet\NewTransactionRequest;
use App\Models\CoinWalletTransaction;
use App\Models\CoinWalletTransactionReason;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionReason;
use App\Payment\Gateways\Mellat\Mellat;
use App\Payment\Gateways\Zarinpal\ZarinPal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class CoinWalletController extends BaseController
{
    protected $coinPrice = 100;


    /**
     * @OA\Get(
     *     path="/api/v1/user/coin-wallet/reasons",
     *     summary="Get all coin wallet transaction reasons",
     *     description="Returns a list of all wallet transaction reasons",
     *     tags={"Coin Wallet"},
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
     *     ),
     * )
     */
    public function getReasons()
    {
        $reasons = CoinWalletTransactionReason::all();

        return $this->sendResponse($reasons, Lang::get('http-statuses.200'));
    }

    /**
     * Show coin wallet detail
     *
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *     path="/api/v1/user/coin-wallet",
     *     summary="Get user's coin wallet",
     *     description="Returns the current user's coin wallet",
     *     operationId="getCoinWallet",
     *     tags={"Coin Wallet"},
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
        $wallet = $user->coinWallet;

        return $this->sendResponse($wallet, Lang::get('http-statuses.200'));
    }

    /**
     * Insert New transaction for coin wallet
     *
     * @param StoreCoinWalletTransactionRequest $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/v1/user/coin-wallet/transaction",
     *     summary="Store a new coin wallet transaction",
     *     description="Store a new coin wallet transaction",
     *     tags={"Coin Wallet"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreCoinWalletTransactionRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function storeTransaction(StoreCoinWalletTransactionRequest $request)
    {
        $validated = $request->all();

        $user =User::find($validated['user_id']);
        $changer = $user;

        $wallet = $user->coinWallet;

        $reason_id = CoinWalletTransactionReason::whereCode($validated['reason_code'])->first()->id;

        $newCoins = $wallet->coins;
        switch ($validated['action']) {
            case 'increase':
                $newCoins = $wallet->coins + $validated['coins'];
                break;
            case 'decrease':
                $newCoins = $wallet->coins - $validated['coins'];
                if ($newCoins < 0) {
                    $message = 'مقدار سکه وارد شده بیشتر از موجودی کیف سکه میباشد';
                    return $this->sendError($message, ['errors' => ['coins' => $message]], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                break;
        }

        $wallet->update(['coins' => $newCoins]);
        $wallet = $wallet->fresh();

        unset($validated['user_id']);
        unset($validated['reason_code']);
        unset($validated['image']);

        $validated['user_id'] = $user->id;
        $validated['final_coins'] = $newCoins;
        $validated['reason_id'] = $reason_id;
        $validated['changer_id'] = $changer->id;

        $transaction = CoinWalletTransaction::query()->create($validated);

        if (!empty($request->image)) {
            $path = $this->uploadNewTransactionImage($request->image, $transaction);
            $transaction->update(['image_path' => $path]);
        }

        return $this->sendResponse(compact('wallet'), 'تراکنش با موفقیت انجام شد');
    }


    /**
     * Store Travel Transaction
     *
     * @param Request $request
     * @return Response
     *
     * @OA\Post(
     *     path="/api/v1/user/coin-wallet/travel-transaction",
     *     summary="Store Travel Transaction",
     *     tags={"Coin Wallet"},
     *     security={ {"sanctum": {} }},
     *     description="Store Travel Transaction and update user's coin wallet balance",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Store Travel Transaction Request body data",
     *         @OA\JsonContent(
     *             required={"travel_id"},
     *             @OA\Property(
     *                 property="travel_id",
     *                 description="ID of the travel",
     *                 type="integer",
     *                 example=1
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function storeTravelTransaction(Request $request)
    {
        $validated = $request->validate([
            'travel_id' => ['required']
        ]);

        $travelCoin = 10; // travel coin

        $user = Auth::user();
        $wallet = $user->coinWallet;

        $newCoins = $wallet->coins - $travelCoin;
        if ($newCoins < 0) {
            return $this->sendError('موجودی کافی نیست', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $reason_id = CoinWalletTransactionReason::whereCode(21)->first()->id; //get new travel reason code

        $wallet->update(['coins' => $newCoins]);
        $wallet = $wallet->fresh();
        CoinWalletTransaction::query()->create([
            'action' => 'decrease',
            'travel_id' => $validated['travel_id'],
            'reason_id' => $reason_id,
            'coins' => $travelCoin,
            'final_coins' => $newCoins,
            'user_id' => $user->id,
            'changer_id' => $user->id
        ]);

        return $this->sendResponse(compact('wallet'), 'تراکنش با موفقیت انجام شد');
    }

    /**
     * Buy coin online
     *
     * @param Request $request
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function buyCoinOnline(Request $request)
    {
        $validated = $request->validate([
            'gateway' => ['required', 'in:' . implode(',', config('payment.active_gateways'))],
            'coins' => ['required', 'numeric']
        ]);

        $coinsAmount = $validated['coins'] * $this->coinPrice;

        $user = Auth::user();

        $gateway = $this->gatewayFactory($validated['gateway']);

        $pay = $gateway->setAmount($coinsAmount)
            ->setCallbackURL(route('coin-wallet::buy-coin.verify-payment'))
            ->setUserID($user->id)
            ->setMobile($user->mobile)
            ->setEmail($user->email)
            ->setDescription('افزایش موجودی کیف سکه')
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
            'amount' => $coinsAmount,
            'status' => 'unpaid'
        ]);

        return $this->sendResponse([
            'payment_url' => $pay->getPayURL()
        ]);
    }

    /**
     * Verify coin payment
     *
     * @param Request $request
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     *

     * @OA\Post(
     *     path="/api/v1/user/coin-wallet/buy-coin/online",
     *     tags={"Coin Wallet"},
     *     security={ {"sanctum": {} }},
     *     summary="Buy Coin Online",
     *     description="Buy coins online using available payment gateways",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Buy Coin Request body data",
     *         @OA\JsonContent(
     *             required={"gateway", "coins"},
     *             @OA\Property(
     *                 property="gateway",
     *                 description="Payment gateway name",
     *                 type="string",
     *                 enum={"zarinpal", "mellat"}
     *             ),
     *             @OA\Property(
     *                 property="coins",
     *                 description="Number of coins to buy",
     *                 type="integer",
     *                 example=10
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function verifyBuyCoinPayment(Request $request)
    {
        if (!empty($request->Authority)) {
            // zarinpal gateway
            return $this->verifyBuyCoinPaymentHandler('zarinpal', $request->Authority);
        } else if (!is_null($request->ResCode)) {
            // mellat gateway
            $resCode = $request->ResCode;
            $refID = $request->RefId;
            $saleRefID = $request->SaleReferenceId;

            if ($resCode != 0) {
                return 'پرداخت انجام نشد!';
            }

            return $this->verifyBuyCoinPaymentHandler('mellat', $refID, ['sale_ref_id' => $saleRefID]);
        }

        return 'فرایند قابل انجام نیست';
    }

    /**
     * Verify buy coin payment handler
     *
     * @param $gatewayName
     * @param $verifyCode
     * @param array $attributes
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verifyBuyCoinPaymentHandler($gatewayName, $verifyCode, array $attributes = [])
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
                $verify = $this->zarinpalVerifyBuyCoin($gateway, $verifyCode, $transaction->amount);
                break;
            case 'mellat':
                $verify = $this->mellatVerifyBuyCoin($gateway, $attributes['sale_ref_id']);
        }

        if (!$verify->isOk()) {
            return 'تایید تراکنش با خطا مواجه شد';
        }

        $user = $transaction->user;
        $wallet = $user->wallet;
        $coinWallet = $user->coinWallet;

        $transaction->update([
            'status' => 'paid',
            'transaction_at' => now()
        ]);

        $newAmount = $wallet->amount + $transaction->amount;

        $coins = $transaction->amount / $this->coinPrice;
        $newCoins = $coinWallet->coins + $coins;

        $coinWallet->update([
            'coins' => $newCoins
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

        // insert wallet transaction for decrease wallet
        $walletTransaction2 = WalletTransaction::query()->create([
            'user_id' => $user->id,
            'amount' => $transaction->amount,
            'action' => 'decrease',
            'final_amount' => $wallet->amount,
            'reason_id' => WalletTransactionReason::query()->whereCode(21)->first()->id,
            'transaction_id' => $transaction->id,
            'changer_id' => $user->id
        ]);

        // insert coin wallet transaction for increase
        $coinWalletTransaction = CoinWalletTransaction::query()->create([
            'action' => 'decrease',
            'reason_id' => CoinWalletTransactionReason::query()->whereCode(11)->first()->id,
            'coins' => $coins,
            'final_coins' => $newCoins,
            'user_id' => $user->id,
            'changer_id' => $user->id,
            'wallet_transaction_id' => $walletTransaction2->id
        ]);

        $walletTransaction2->update([
            'coin_wallet_transaction_id' => $walletTransaction2->id
        ]);

        return 'پرداخت با شماره پیگیری ' . $verify->getReferenceID() . ' با موفقیت انجام شد';
    }

    /**
     * Zarinpal verify buy coin
     *
     * @param ZarinPal $instance
     * @param $authority
     * @param $amount
     * @return ZarinPal
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function zarinpalVerifyBuyCoin(ZarinPal $instance, $authority, $amount)
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
    public function mellatVerifyBuyCoin(Mellat $instance, $saleRefId)
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
        $imagePath = 'uploads/transaction/coin-wallet/' . $transaction->id . '/';
        $fileName = $this->uploadFile($file, $imagePath);

        return $imagePath . $fileName;
    }
}
