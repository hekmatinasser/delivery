<?php

namespace App\Models;

use App\Enums\LogActionsEnum;
use App\Enums\LogModelsEnum;
use App\Enums\LogUserTypesEnum;
use App\Models\admin\Admin;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        if ($this->userType == 0)
            return $this->belongsTo(User::class, 'user_id');
        else
            return $this->belongsTo(Admin::class, 'user_id');
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
            '6' => 'موفق',
            '7' => 'نا موفق',
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
            'ForgotPass' => 'فراموشی رمزعبور',
            'ResetPass' => 'تغییر رمزعبور',
            'Verify' => 'تایید شماره تلفن',
            'Logout' => 'خروج از سیستم',
            'User' => 'کاربر',
            'Vehicle' => 'وسیله نقلیه',
        ];
    }

    /**
     * Store a log entry for a user action
     *
     * @param LogUserTypesEnum $userType The type of user (e.g. 'admin', 'user')
     * @param int $userId The ID of the user performing the action
     * @param LogModelsEnum $model The model being acted upon (e.g. 'Login', 'Register')
     * @param LogActionsEnum $action The action being performed (e.g. 'create', 'update', 'delete')
     * @param string|null $title The title of the item being acted upon (optional)
     * @param int|null $itemID The ID of the item being acted upon (optional)
     *
     * @return void
     */
    static function store(LogUserTypesEnum $userType, $userId, LogModelsEnum $model, LogActionsEnum $action, $title = null, $itemID = null)
    {
        $last = Log::where('created_at', '>=', Carbon::now()->subSecondS(30)->toDateTimeString())
            ->where([
                'userType' => $userType,
                'user_id' => $userId,
                'model' => $model,
                'action' => $action,
            ])
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
