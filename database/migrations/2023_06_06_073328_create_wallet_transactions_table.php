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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->integer('amount');
            $table->enum('action', ['increase', 'decrease']);
            $table->integer('final_amount');
            $table->unsignedBigInteger('reason_id');
            $table->foreign('reason_id')->references('id')->on('wallet_transaction_reasons')->cascadeOnDelete();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete();
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
        Schema::dropIfExists('wallet_transactions');
    }
};
