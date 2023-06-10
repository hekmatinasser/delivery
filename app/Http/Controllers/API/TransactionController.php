<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\StoretransactionRequest;
use App\Http\Requests\UpdatetransactionRequest;
use App\Models\Log;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends BaseController
{



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|max:255',
            'transaction_number' => 'required|max:255',
            'transaction_status' => 'required|max:255',
            'price' => 'required|max:255',
            'user_id' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        Transaction::create($input);
        Log::store(0, Auth::user()->id, 'Transaction', 0);

        return $this->sendResponse('', 'تراکنش با موفقیت ایجاد شد');


    }

    /**
     * Display the specified resource.
     */
    public function show(transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatetransactionRequest $request, transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(transaction $transaction)
    {
        //
    }
}
