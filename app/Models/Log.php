<?php

namespace App\Models;

use App\Models\admin\Admin;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Log extends Model
{
    protected $table = 'log';
    protected $fillable = [
        'userType',
        'user_id',
        'model',
        'action',
        'title',
        'item_id',
    ];

    public function user(){
        if ($this->userType == 0)
            return $this->belongsTo(User::class , 'user_id');
        else
            return $this->belongsTo(Admin::class , 'user_id');
    }

    public function userTypes(): array
    {
        return [
            '0' => 'کاربران',
            '1' => 'مدیران',
        ];
    }

    public function actions(): array
    {
        return [
            '0' => 'اضافه کردن',
            '1' => 'مشاهده جزئیات',
            '2' => 'ویرایش کردن',
            '3' => 'حذف کردن',
            '4' => 'مشاهده همه',
            '5' => 'درخواست',
            '9' => 'ثبت نام',
            '10' => 'مشاهده پروفایل',
            '11' => 'ویرایش پروفایل',
        ];
    }

    public function models(): array
    {
        return [
            'Login' => 'ورود',
            'Register' => 'ثبت نام',
            'ResetPass' => 'فراموشی رمزعبور',
            'Verify' => 'تایید شماره تلفن',
            'Logout' => 'خروج از سیستم',
            'User' => 'کاربر',
            'Vehicle' => 'وسیله نقلیه',
        ];
    }

    static function store($userType, $userId, $model, $action, $title = null, $itemID = null)
    {
        $last = Log::where('created_at', '>=', Carbon::now()->subSecond(30)->toDateTimeString())
            ->where(['userType' => $userType,
                'user_id' => $userId,
                'model' => $model,
                'action' => $action,])
            ->first();
        if (!$last)
            Log::create([
                'userType' => $userType,
                'user_id' => $userId,
                'model' => $model,
                'action' => $action,
                'title' => $title,
                'item_id' => $itemID,
            ]);
    }
}
