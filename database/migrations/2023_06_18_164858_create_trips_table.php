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
            $table->increments('id');
            $table->string('trip_code')->unique();
            $table->integer('vehicle_type')->default(0);
            $table->unsignedInteger('store_id');
            $table->foreign('store_id')->references('id')->on('store');
            $table->unsignedInteger('vehicle_id')->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicle');
            $table->unsignedInteger('origin');
            $table->foreign('origin')->references('id')->on('neighborhoods');
            $table->unsignedInteger('destination');
            $table->foreign('destination')->references('id')->on('neighborhoods');
            $table->dateTime('request_registration_time');
            $table->dateTime('shipment_prepare_time');
            $table->dateTime('arrive_time')->nullable();
            $table->dateTime('deliver_time')->nullable();
            $table->decimal('trip_rial_fare')->nullable();
            $table->integer('status');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('description')->nullable();
            $table->text('manager_description')->nullable();
            $table->dateTime('expire');
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
