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
        Schema::create('store', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
            $table->unsignedInteger('category_id');
            $table->foreign('category_id')->references('id')->on('store_category')->onUpdate('cascade');
            $table->integer('areaType')->unsigned()->nullable();
            $table->string('address')->nullable();
            $table->string('postCode')->nullable();
            $table->integer('phone')->nullable();
            $table->string('name')->nullable();
            $table->decimal('lat')->nullable();
            $table->decimal('lang')->nullable();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store');
    }
};
