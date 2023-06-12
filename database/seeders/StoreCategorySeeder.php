<?php

namespace Database\Seeders;

use App\Models\CoinWalletTransactionReason;
use App\Models\StoreCategory;
use Illuminate\Database\Seeder;

class StoreCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            [
                'title' => 'ساندویچی',
            ],
            [
                'title' => 'لباس فروشی',
            ],
        ];

        if(StoreCategory::query()->count() < 1) {
            foreach ($reasons as $reason) {
                StoreCategory::create($reason);
            }
        }
    }
}
