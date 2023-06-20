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
        Schema::create('trip_feed_backs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trip_code');
            $table->string('vehicle_user_code');
            $table->string('shop_user_code');
            $table->dateTime('vehicle_feedback_time')->nullable();
            $table->integer('vehicle_rating')->nullable();
            $table->text('vehicle_description')->nullable();
            $table->dateTime('shop_feedback_time')->nullable();
            $table->integer('shop_rating')->nullable();
            $table->text('shop_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_feed_backs');
    }
};
