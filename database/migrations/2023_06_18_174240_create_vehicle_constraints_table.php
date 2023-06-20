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
        Schema::create('vehicle_constraints', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_code');
            $table->foreignId('user_id')->constrained();
            $table->dateTime('constraint_registration_time')->nullable();
            $table->text('quarantined_neighborhood')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_constraints');
    }
};
