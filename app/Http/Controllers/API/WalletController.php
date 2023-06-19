<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\BuyCoinRequest;
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
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class WalletController extends BaseController
{
    use FileHandler;


    /**
     * @OA\Get(
     *     path="/api/v1/user/wallet/reasons",
     *     summary="Get all wallet transaction reasons",
     *     description="Returns a list of all wallet transaction reasons",
     *     tags={"Wallet"},
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
        $reasons = WalletTransactionReason::all();

        return $this->sendResponse($reasons, Lang::get('http-statuses.200'));
    }

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
     *     tags={"Wallet"},
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
     *
     * @OA\Post(
     *     path="/api/v1/user/wallet/transaction",
     *     summary="Create a new wallet transaction",
     *     description="Creates a new wallet transaction for the current user",
     *     operationId="storeTransaction",
     *     tags={"Wallet"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Wallet transaction data",
     *         @OA\JsonContent(ref="#/components/schemas/StoreWalletTransactionRequest")
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
    public function storeTransaction(StoreWalletTransactionRequest $request)
    {
        $user = Auth::user();
        try {
            $response = $this->addTransaction($request, $user, $user->id);
            return $this->sendResponse($response['wallet'], 'تراکنش با موفقیت انجام شد');
        } catch (Exception $e) {
            if ($e->getCode() == 500)
                return $this->sendError($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
            return $this->sendError($e->getMessage(), ['errors' => ['amount' => $e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Buy coin with wallet
     *
     * @param Request $request
     * @return Response
     *
     * @OA\Post(
     *     path="/api/v1/user/wallet/buy-coin",
     *     summary="Buy coin",
     *     description="Buy coins using wallet balance",
     *     tags={"Wallet"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         description="Buy coin request body",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BuyCoinRequest")
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
    public function buyCoin(BuyCoinRequest $request)
    {
        $coinPrice = 100; // each coin price 10,000 IRT

        $user = Auth::user();
        $wallet = $user->wallet;
        $coinWallet = $user->coinWallet;

        if ($request->amount > $wallet->amount) {
            $message = 'مقدار مبلغ وارد شده بیشتر از موجودی کیف پول میباشد';
            return $this->sendError($message, ['errors' => ['amount' => $message]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->amount < $coinPrice) {
            $message = 'مبلغ وارد شده کمتر از هزینه یک سکه است، هزینه هر سکه ' . $coinPrice . ' هزار تومان است';
            return $this->sendError($message, ['errors' => ['amount' => $message]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // convert input amount to coins
        $coins = intVal($request->amount / $coinPrice);
        // $newCoins = $coinWallet->coins + $coins;
        // $newAmount = $wallet->amount - ($coins * $coinPrice);

        DB::beginTransaction();
        try {
            // insert wallet transaction for buy coin
            try {
                $res = $this->addTransaction([
                    'amount' => ($coins * $coinPrice),
                    'action' => 'decrease',
                    'reason_code' => 21,
                ], $user, $user->id);

                $walletTransaction = $res['transaction'];
            } catch (Exception $e) {
                DB::rollBack();
                return $this->sendError($e->getMessage(), ['errors' => [$e->getMessage()]], $e->getCode());
            }

            // insert coin wallet transaction for buy coin

            try {
                $res = $this->addWalletTransaction([
                    'amount' => $coins,
                    'action' => 'increase',
                    'reason_code' => 11,
                    'wallet_transaction_id' => $walletTransaction->id
                ], $user, $user->id);

                $coinWalletTransaction = $res['coinWalletTransaction'];
                $coinWallet = $res['coinWallet'];
            } catch (Exception $e) {
                DB::rollBack();
                return $this->sendError($e->getMessage(), ['errors' => [$e->getMessage()]], $e->getCode());
            }

            // add coin wallet transaction id to wallet transaction
            $walletTransaction->update([
                'coin_wallet_transaction_id' => $coinWalletTransaction->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), Lang::get('http_statuses.500'), 500);
        }

        return $this->sendResponse(compact('wallet', 'coinWallet'), 'تراکنش با موفقیت انجام شد');
    }

    /**
     * Increase wallet with online gateway
     *
     * @param Request $request
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     *  * @OA\Post(
     *     path="/api/wallet/increase-online",
     *     summary="Increase wallet balance through online payment",
     *     tags={"Wallet"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Payment request body",
     *         @OA\JsonContent(
     *             @OA\Property(property="gateway", type="string", enum={"zarinpal", "mellat"}, example="zarinpal", description="Payment gateway"),
     *             @OA\Property(property="amount", type="number", example="100000", description="Amount to be paid")
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
        return $file->store('transaction_photos');
    }


    protected function addTransaction($request, $user, $changerId)
    {
        try {
            DB::beginTransaction();
            $wallet = $user->wallet;
            $reason_id =  WalletTransactionReason::whereCode($request['reason_code'])->first()->id;
            $newAmount = $wallet->amount;
            switch ($request['action']) {
                case 'increase':
                    $newAmount = $wallet->amount + $request['amount'];
                    break;
                case 'decrease':
                    $newAmount = $wallet->amount - $request['amount'];
                    if ($newAmount < 0) {
                        throw new Exception('مقدار مبلغ وارد شده بیشتر از موجودی کیف پول میباشد', Response::HTTP_BAD_REQUEST);
                    }
                    break;
                default:
                    throw new Exception('ّInput Error', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $wallet->update(['amount' => $newAmount]);
            $wallet = $wallet->fresh();
            $input['user_id'] = $user->id;
            $input['final_amount'] = $newAmount;
            $input['reason_id'] = $reason_id;
            $input['changer_id'] = $changerId;
            $input['amount'] = $request['amount'];
            if (!empty($request['description'])) {
                $input['description'] = $request['description'];
            }
            $transaction = WalletTransaction::query()->create($input);
            if (!empty($request['image'])) {
                $path = $this->uploadNewTransactionImage($request['image'], $transaction);
                $transaction->update(['image_path' => $path]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), $e->getCode());
        }
        return compact('wallet', 'transaction');
    }


    protected function addWalletTransaction($request, $user, $changerId)
    {
        try {
            DB::beginTransaction();
            $coinWallet = $user->coinWallet;
            $reason_id =  CoinWalletTransactionReason::query()->whereCode($request['reason_code'])->first()->id;
            $newCoins = $coinWallet->coins;

            switch ($request['action']) {
                case 'increase':
                    $newCoins = $coinWallet->coins + $request['amount'];
                    break;
                case 'decrease':
                    $newCoins = $coinWallet->coins - $request['amount'];
                    if ($newCoins < 0) {
                        throw new Exception('مقدار سکه وارد شده بیشتر از موجودی سکه میباشد', Response::HTTP_BAD_REQUEST);
                    }
                    break;
                default:
                    throw new Exception('ّInput Error', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $coinWallet->update(['coins' => $newCoins]);
            $coinWallet = $coinWallet->fresh();

            $coinWalletTransaction = CoinWalletTransaction::query()->create([
                'user_id' => $user->id,
                'coins' => $request['amount'],
                'action' => $request['action'],
                'final_coins' => $newCoins,
                'reason_id' => $reason_id,
                'changer_id' => $changerId,
            ]);

            if (!empty($request['wallet_transaction_id'])) {
                $coinWalletTransaction->update(['wallet_transaction_id' => $request['wallet_transaction_id']]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), $e->getCode());
        }
        return compact('coinWallet', 'coinWalletTransaction');
    }
}
