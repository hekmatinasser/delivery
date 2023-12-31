<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\CoinSetting;
use App\Models\CoinWalletTransactionReason;
use App\Models\Neighborhood;
use App\Models\Setting;
use App\Models\Store;
use App\Models\Vehicle;
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
        // \App\Models\Setting::factory(1)->create();
        Setting::create([
            'wrong_login_try_per_10_min' => 4,
            'wrong_login_try_per_day' => 4,
            'website_name' => 'website',
            'website_url' => 'website_url',
            'application_name' => 'application_name',
            'pay_coin_per_trip_with_vehicle' => 1,
            'pay_coin_per_trip_with_store' => 2,
            'pay_for_each_coin_with_vehicle' => 1000,
            'pay_for_each_coin_with_store' => 1200,
            'delay_accepting_with_vehicle' => 60,
            'delay_reaching_with_vehicle' => 15,
            'delay_delivering_with_vehicle' => 35,
            'delay_delivering_with_store' => 10,
            'payment_gateway' => 'zarinpal',
            'zarin_merchant' => 'zarin_merchant',
        ]);

        // if (config('app.env') == 'local') {
        //     $user = \App\Models\User::factory()->create([
        //         'mobile' => '09123456789',
        //         'status' => '1',
        //         'userType' => '0',
        //         'password' => '$2y$10$x.D2SM4Oh0neDkWpdrBF5.UOB5LSJxE.wTubI3shur0TdgDz4qOAW', // MyNewPassword123
        //     ]);

        //     \App\Models\Wallet::factory()->create([
        //         'user_id' => $user->id,
        //         'amount' => 1000
        //     ]);

        //     \App\Models\CoinWallet::factory()->create([
        //         'user_id' => $user->id,
        //         'coins' => 1
        //     ]);

        //     $userStore = \App\Models\User::factory()->create([
        //         'mobile' => '09000000001',
        //         'status' => '1',
        //         'userType' => '1'
        //     ]);

        //     \App\Models\Wallet::factory()->create([
        //         'user_id' => $userStore->id,
        //         'amount' => 0
        //     ]);

        //     \App\Models\CoinWallet::factory()->create([
        //         'user_id' => $userStore->id,
        //         'coins' => 0
        //     ]);
        //     $neighborhoods = [
        //         [
        //             'name' => 'محله اول',
        //             'user_id' => 1,
        //             'code' => 'code__1',
        //             'status' => 1
        //         ],
        //         [
        //             'name' => 'محله دوم',
        //             'user_id' => 1,
        //             'code' => 'code__2',
        //             'status' => 1
        //         ],
        //     ];


        //     foreach ($neighborhoods as $key => $value) {
        //         Neighborhood::create($value);
        //     }


        //     $stores = [
        //         [
        //             'user_id' => 1,
        //             'category_id' => 1,
        //             'neighborhood_id' => 1,
        //             'name' => 'store one'
        //         ],
        //     ];
        //     foreach ($stores as $key => $value) {
        //         Store::create($value);
        //     }

        //     $vehicles = [
        //         [
        //             'user_id' => 1,
        //             'type' => 0,
        //             'brand' => 'honda',
        //             'pelak' => '123456',
        //             'color' => 'red',
        //             'model' => 'cg125'
        //         ],
        //     ];
        //     foreach ($vehicles as $key => $value) {
        //         Vehicle::create($value);
        //     }
        // }

        CoinSetting::create([
            'id' => '1',
            'shop_coin_fee' => 1,
            'vehicle_coin_fee' => 1,
            'shop_coin_rial_fee' => 1,
            'motor_coin_rial_fee' => 1,
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