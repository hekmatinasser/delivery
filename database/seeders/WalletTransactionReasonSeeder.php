<?php

namespace Database\Seeders;

use App\Models\CoinWalletTransactionReason;
use App\Models\WalletTransactionReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletTransactionReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            [
                'type' => 'شارژ کیف پول',
                'code' => 11,
                'description' => 'شارژ کیف پول توسط تراکنش مالی'
            ],
            [
                'type' => 'خرید سکه',
                'code' => 21,
                'description' => 'خرید سکه با کیف پول'
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

        if(WalletTransactionReason::query()->count() < 1) {
            foreach ($reasons as $reason) {
                WalletTransactionReason::create($reason);
            }
        }
    }
}
