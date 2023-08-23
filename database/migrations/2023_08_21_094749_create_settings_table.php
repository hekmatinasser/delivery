<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->integer('wrong_login_try_per_10_min')->default(4);
            $table->integer('wrong_login_try_per_day')->default(4);
            $table->string('website_name')->default('website_name');
            $table->string('website_url')->default('website_url');
            $table->string('application_name')->default('application_name');
            $table->text('logo_admin')->nullable();
            $table->text('logo_store')->nullable();
            $table->text('logo_vehicle')->nullable();
            $table->text('favicon')->nullable();
            $table->integer('pay_coin_per_trip_with_vehicle')->default(1);
            $table->integer('pay_coin_per_trip_with_store')->default(2);
            $table->integer('pay_for_each_coin_with_vehicle')->default(1000);
            $table->integer('pay_for_each_coin_with_store')->default(1200);
            $table->integer('delay_accepting_with_vehicle')->default(60);
            $table->integer('delay_reaching_with_vehicle')->default(15);
            $table->integer('delay_delivering_with_vehicle')->default(25);
            $table->integer('delay_delivering_with_store')->default(10);
            $table->integer('max_active_trip_with_vehicle')->default(2);
            $table->integer('travel_expire_approve_time')->default(1);
            $table->integer('travel_expire_pending_time')->default(1);
            $table->string('payment_gateway')->default('zarin');
            $table->string('zarin_merchant')->default('carin_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};