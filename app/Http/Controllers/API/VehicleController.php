<?php

namespace App\Http\Controllers\API;

use App\Models\Log;
use App\Models\Vehicle;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class VehicleController extends BaseController
{
    public function types()
    {
        $v = new Vehicle();
        return $this->sendResponse($v->types(), 'Vehicle Types');
    }
    public function my(Request $request)
    {
        $user = Auth::user();
        $all = [];
        foreach (Vehicle::where('user_id' , $user->id)->get()as $index => $item) {
            $all[$index]['type'] = $item->types($item->type);
            $all[$index]['brand'] = $item->brand;
            $all[$index]['pelak'] = $item->pelak;
            $all[$index]['color'] = $item->color;
            $all[$index]['model'] = $item->model;
        }
        Log::store(0, $user->id, 'Vehicle', 1);
        return $this->sendResponse($all, 'All Vehicle');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|max:255',
            'brand' => 'required|max:255',
            'pelak' => 'required|max:255',
            'color' => 'required|max:255',
            'model' => 'required|max:255',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        Vehicle::create($input);
        Log::store(0, Auth::user()->id, 'Vehicle', 0);

        return $this->sendResponse('', 'وسیله نقلیه با موفقیت ایجاد شد');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicleID' => 'required',
            'type' => 'required|max:255',
            'brand' => 'required|max:255',
            'pelak' => 'required|max:255',
            'color' => 'required|max:255',
            'model' => 'required|max:255',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $vehicle = Vehicle::where('id', $request->vehicleID)->where('user_id' , Auth::user()->id)->first();
        if($vehicle)
            $vehicle->update($input);
        else
            return $this->sendError('Error.', 'وسیله نقلیه یافت نشد');

        Log::store(0, Auth::user()->id, 'Vehicle', 2);
        return $this->sendResponse('', 'بروزرسانی با موفقیت انجام شد');
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicleID' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $vehicle = Vehicle::where('id', $request->vehicleID)->where('user_id' , Auth::user()->id)->first();
        if($vehicle) {
            $vehicle->delete();
            Log::store(0, Auth::user()->id, 'Vehicle', 3);
            return $this->sendResponse('', 'وسیله نقلیه با موفقیت حذف شد');
        }
        else
            return $this->sendError('Error.', 'وسیله نقلیه یافت نشد');
    }


}
