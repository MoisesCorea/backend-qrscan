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
            $table->decimal('phone_number', 15, 0);
            $table->string('profile_image');
            $table->string('qr_image');
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('department_id');
            $table->string('status');
            $table->timestamps();

            $table->foreign('shift_id')->references('id')->on('shifts');
            $table->foreign('department_id')->references('id')->on('departments');
            
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
