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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('family')->nullable();
            $table->string('mobile', 11)->unique();
            $table->string('nationalCode')->nullable();
            $table->string('nationalPhoto')->nullable();
            $table->integer('status')->default("0")->unsigned();
            $table->integer('unValidCodeCount')->unsigned()->default("1");
            $table->string('address')->nullable();
            $table->string('postCode')->nullable();
            $table->string('phone')->nullable();
            $table->string('userType')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
