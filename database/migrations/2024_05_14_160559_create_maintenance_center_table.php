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
        Schema::create('maintenance_center', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_arabic');
            $table->string('address');
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->integer('phone_number')->nullable();
            $table->string('time')->nullable();
            $table->string('direction_button')->nullable();
            $table->integer('city')->nullable();
            $table->integer('sort')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_center');
    }
};
