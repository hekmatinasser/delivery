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

        if (config('app.env') == 'local') {
            $user = \App\Models\User::factory()->create([
                'mobile' => '09123456789',
                'status' => '1',
                'userType' => '0',
                'password' => '$2y$10$x.D2SM4Oh0neDkWpdrBF5.UOB5LSJxE.wTubI3shur0TdgDz4qOAW', // MyNewPassword123
            ]);

            \App\Models\Wallet::factory()->create([
                'user_id' => $user->id,
                'amount' => 1000
            ]);

            \App\Models\CoinWallet::factory()->create([
                'user_id' => $user->id,
                'amount' => 1
            ]);
        }

        $this->call([
            RoleTableSeeder::class,
            PermissionTableSeeder::class,
            WalletTransactionReasonSeeder::class,
            CoinWalletTransactionReasonSeeder::class,
            StoreCategorySeeder::class
        ]);
    }
}
