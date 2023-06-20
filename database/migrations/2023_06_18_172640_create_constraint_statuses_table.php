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
        Schema::create('constraint_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('constraint_id');
            $table->string('constraint_code');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->datetime('constraint_registration_time');
            $table->text('changes')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constraint_statuses');
    }
};
