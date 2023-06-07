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
        Schema::create('coin_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->integer('coins');
            $table->enum('action', ['increase', 'decrease']);
            $table->integer('final_coins');
            $table->unsignedBigInteger('reason_id');
            $table->foreign('reason_id')->references('id')->on('coin_wallet_transaction_reasons')->cascadeOnDelete();
            $table->unsignedBigInteger('wallet_transaction_id')->nullable();
            $table->foreign('wallet_transaction_id')->references('id')->on('wallet_transactions')->cascadeOnDelete();
            $table->unsignedBigInteger('travel_id')->nullable();
            $table->unsignedInteger('changer_id');
            $table->foreign('changer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_wallet_transactions');
    }
};
