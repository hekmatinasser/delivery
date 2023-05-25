<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Log;

class LogController extends Controller
{
    public function admins()
    {
        Log::store(1, \Illuminate\Support\Facades\Auth::guard('admin')->user()->id, 'Log', 25);
        $page_title = "لیست عملکرد مدیران";
        $all = Log::where('userType', 1)->get();
        $isAll = true;
        return view('adminViews.log.admin', compact('page_title', 'all', 'isAll'));
    }

    public function admin($userId)
    {
        $log = Log::where('userType', 1)->where('user_id', $userId)->first();
        if ($log) {
            if ($log->user) {
                $page_title = "لیست عملکرد :" . $log->user->name;
                $all = Log::where('userType', 1)->where('user_id', $userId)->get();
                return view('adminViews.log.admin', compact('page_title', 'all'));
            }
        }
        return redirect()->back();
    }


    public function users()
    {
        Log::store(1, \Illuminate\Support\Facades\Auth::guard('admin')->user()->id, 'Log', 25);
        $page_title = "لیست عملکرد کاربران";
        $all = Log::where('userType', 0)->get();
        $isAll = true;
        return view('adminViews.log.user', compact('page_title', 'all', 'isAll'));
    }

    public function user($userId)
    {
        $log = Log::where('userType', 0)->where('user_id', $userId)->first();
        if ($log) {
            if ($log->user) {
                $page_title = "لیست عملکرد :" . $log->user->name;
                $all = Log::where('userType', 0)->where('user_id', $userId)->get();
                return view('adminViews.log.user', compact('page_title', 'all'));
            }
        }
        return redirect()->back();
    }


}






