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
        Schema::create('inter_neighborhood_fares', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedInteger('origin');
            $table->foreign('origin')->references('id')->on('neighborhoods');
            $table->unsignedInteger('destination');
            $table->foreign('destination')->references('id')->on('neighborhoods');
            $table->string('original')->index();
            $table->string('reverse')->index();
            $table->bigInteger('fare');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inter_neighborhood_fares');
    }
};
