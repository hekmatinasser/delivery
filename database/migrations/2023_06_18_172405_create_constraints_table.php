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
        Schema::create('constraints', function (Blueprint $table) {
            $table->increments('id');
            $table->string('constraint_code');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->dateTime('constraint_time_register')->nullable();
            $table->dateTime('constraint_end_time')->nullable();
            $table->string('prohibition_code')->nullable();
            $table->string('constrained_user')->nullable();
            $table->string('quarantined_users')->nullable();
            $table->string('quarantined_neighborhood')->nullable();
            $table->integer('constraint_status')->default(3);
            $table->timestamps();
        });
    }

    /**
 * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constraints');
    }
};
