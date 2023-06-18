<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_code')->unique();
            $table->string('shop_code');
            $table->string('vehicle_code')->nullable();
            $table->string('destination');
            $table->dateTime('request_registration_time');
            $table->dateTime('shipment_prepare_time');
            $table->decimal('trip_rial_fare');
            $table->integer('status');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('description')->nullable();
            $table->text('manager_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
