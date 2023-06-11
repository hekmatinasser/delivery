<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifyCode extends Model
{
    protected $table = 'verify_code';
    protected $fillable = [
        'mobile',
        'code',
    ];

    public function createNewCode($mobile): string
    {
        VerifyCode::where('mobile', $mobile)->delete();
        $code = rand(1000, 9999);
        VerifyCode::create([
            'code' => $code,
            'mobile' => $mobile,
        ]);
        return $code;
    }
}
