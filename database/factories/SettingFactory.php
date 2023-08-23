<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
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
        ];
    }
}