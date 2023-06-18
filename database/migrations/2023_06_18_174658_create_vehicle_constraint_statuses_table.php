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
        Schema::create('vehicle_constraint_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_constraint_id')->constrained();
            $table->string('vehicle_code');
            $table->foreignId('user_id')->constrained();
            $table->dateTime('constraint_registration_time')->nullable();
            $table->string('quarantined_neighborhood')->nullable();
            $table->text('descrtiption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_constraint_statuses');
    }
};
