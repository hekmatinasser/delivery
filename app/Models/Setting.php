<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'wrong_login_try_per_10_min',
        'wrong_login_try_per_day',
        'website_name',
        'website_url',
        'application_name',
        'pay_coin_per_trip_with_vehicle',
        'pay_coin_per_trip_with_store',
        'pay_for_each_coin_with_vehicle',
        'pay_for_each_coin_with_store',
        'delay_accepting_with_vehicle',
        'delay_reaching_with_vehicle',
        'delay_delivering_with_vehicle',
        'delay_delivering_with_store',
        'payment_gateway',
        'zarin_merchant',
    ];
}