<?php

namespace Tests\Feature\API;

use App\Models\CoinWallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class CoinWalletTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->user = User::factory()->create();
    }

    /**
     * test
     */
    public function test_can_show_coin_wallet()
    {
        $coinWallet = CoinWallet::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson(route('coin-wallet::show'));
        $response->assertSuccessful();
    }

    /**
     * test
     */
    public function test_can_store_new_increase_transaction()
    {
        $coinWallet = CoinWallet::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(route('coin-wallet::store-transaction'), [
            'action' => 'increase',
            'coins' => 100,
            'reason_code' => 11,
        ]);
        $response->assertSuccessful();
    }

    /**
     * test
     */
    public function test_store_new_decrease_coins_should_be_less_that_wallet_coins()
    {
        $coinWallet = CoinWallet::factory()->create(['user_id' => $this->user->id, 'coins' => 20]);

        $response = $this->actingAs($this->user)->postJson(route('coin-wallet::store-transaction'), [
            'action' => 'decrease',
            'coins' => 100,
            'reason_code' => 11,
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * test
     */
    public function test_can_store_new_decrease_transaction()
    {
        $coinWallet = CoinWallet::factory()->create(['user_id' => $this->user->id, 'coins' => 200]);

        $response = $this->actingAs($this->user)->postJson(route('coin-wallet::store-transaction'), [
            'action' => 'decrease',
            'coins' => 100,
            'reason_code' => 11,
        ]);
        $response->assertSuccessful();
    }

    /**
     * test
     */
    public function test_can_user_store_new_travel_transaction()
    {
        $coinWallet = CoinWallet::factory()->create(['user_id' => $this->user->id, 'coins' => 200]);

        $response = $this->actingAs($this->user)->postJson(route('coin-wallet::store-travel-transaction'), [
            'reason_code' => 21,
            'travel_id' => 143
        ]);
        $response->assertSuccessful();
    }

    /**
     * test | uncomment to test zarinpal gateway to increase wallet
     */
    public function test_can_buy_coin_online_with_zarinpal()
    {
        $coinWallet = CoinWallet::factory()->create(['user_id' => $this->user]);
        $response = $this->actingAs($this->user)->postJson(route('coin-wallet::buy-coin-online'),[
            'gateway' => 'zarinpal',
            'coins' => 2000
        ]);

        $response->assertSuccessful();
    }
}