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
            $table->string('id', 10)->primary();
            $table->string('name');
            $table->string('last_name');
            $table->integer('age');
            $table->string('gender');
            $table->string('email');
            $table->string('address');
            $table->integer('phone_number');
            $table->string('profile_image');
            $table->string('qr_image');
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('deparment_id');
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('shift_id')->references('id')->on('shifts');
            $table->foreign('deparment_id')->references('id')->on('departments');
            
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
