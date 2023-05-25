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
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('store_category')->onUpdate('cascade');
            $table->integer('areaType')->unsigned()->nullable();
            $table->string('address')->nullable();
            $table->string('postCode', 20)->nullable();
            $table->integer('phone', 15)->nullable();
            $table->string('name')->nullable();
            $table->string('lot', 100)->nullable();
            $table->string('lang' ,100)->nullable();
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
