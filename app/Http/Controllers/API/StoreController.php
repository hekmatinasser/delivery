<?php

namespace App\Http\Controllers\API;

use App\Models\Log;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\JsonResponse;

class StoreController extends BaseController
{
    public function areaTypes()
    {
        $v = new Store();
        return $this->sendResponse($v->areaTypes(), 'Store Types');
    }

    public function categories()
    {
        return $this->sendResponse(StoreCategory::select('title')->get(), 'Store Categories');
    }

    public function my(Request $request): JsonResponse
    {
        $user = Auth::user();
        $all = [];
        foreach (Store::where('user_id' , $user->id)->get()as $index => $item) {
            $all[$index]['category'] = $item->category ? $item->category->title : '';
            $all[$index]['areaType'] = $item->areaTypes($item->areaType);
            $all[$index]['name'] = $item->name;
            $all[$index]['address'] = $item->address;
            $all[$index]['postCode'] = $item->postCode;
            $all[$index]['lot'] = $item->lot;
            $all[$index]['lang'] = $item->lang;
            $all[$index]['phone'] = $item->phone;
        }
        Log::store(0, $user->id, 'Store', 1);
        return $this->sendResponse($all, 'All Store');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required',
            'areaType' => 'required',
            'name' => 'required',
            'address' => 'required',
            'postCode' => 'required',
            'lot' => 'required',
            'lang' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        Store::create($input);
        Log::store(0, Auth::user()->id, 'Store', 0);

        return $this->sendResponse('', 'مغازه با موفقیت ایجاد شد');
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'storeID' => 'required',
            'category' => 'required',
            'areaType' => 'required',
            'name' => 'required',
            'address' => 'required',
            'postCode' => 'required',
            'lot' => 'required',
            'lang' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $vehicle = Store::where('id', $request->storeID)->where('user_id' , Auth::user()->id)->first();
        if($vehicle)
            $vehicle->update($input);
        else
            return $this->sendError('Error.', 'مغازه یافت نشد');

        Log::store(0, Auth::user()->id, 'Store', 2);
        return $this->sendResponse('', 'بروزرسانی با موفقیت انجام شد');
    }

    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'storeID' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $vehicle = Store::where('id', $request->storeID)->where('user_id' , Auth::user()->id)->first();
        if($vehicle) {
            $vehicle->delete();
            Log::store(0, Auth::user()->id, 'Store', 3);
            return $this->sendResponse('', 'مغازه با موفقیت حذف شد');
        }
        else
            return $this->sendError('Error.', 'مغازه یافت نشد');
    }


}
