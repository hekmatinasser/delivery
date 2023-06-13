<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\CoinWalletTransactionReason;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'id' => '1',
            'mobile' => '09000000000',
            'status' => '1',
            'userType' => '1'
        ]);


        $this->call([
            RoleTableSeeder::class,
            PermissionTableSeeder::class,
            WalletTransactionReasonSeeder::class,
            CoinWalletTransactionReasonSeeder::class,
            StoreCategorySeeder::class
        ]);
    }
}
