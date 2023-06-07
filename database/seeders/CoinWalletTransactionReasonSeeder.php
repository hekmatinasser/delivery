<?php

namespace Database\Seeders;

use App\Models\CoinWalletTransactionReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoinWalletTransactionReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            [
                'type' => 'خرید سکه',
                'code' => 11,
                'description' => 'خرید سکه از موجودی کیف پول'
            ],
            [
                'type' => 'سفر',
                'code' => 21,
                'description' => 'ثبت سفر'
            ],
            [
                'type' => 'سفر',
                'code' => 22,
                'description' => 'لغو سفر توسط خود شخص'
            ],
            [
                'type' => 'سفر',
                'code' => 23,
                'description' => 'لغو سفر توسط طرف مقابل'
            ],
            [
                'type' => 'سفر',
                'code' => 24,
                'description' => 'لغو سفر توسط مدیریت'
            ],
            [
                'type' => 'مدیریت',
                'code' => 31,
                'description' => 'هدیه از طرف مدیریت'
            ],
            [
                'type' => 'مدیریت',
                'code' => 32,
                'description' => 'جریمه از طرف مدیریت'
            ],
            [
                'type' => 'مدیریت',
                'code' => 33,
                'description' => 'بازگشت سکه و شارژ کیف پول توسط مدیریت'
            ],
        ];

        if(CoinWalletTransactionReason::query()->count() < 1) {
            foreach ($reasons as $reason) {
                CoinWalletTransactionReason::create($reason);
            }
        }
    }
}
