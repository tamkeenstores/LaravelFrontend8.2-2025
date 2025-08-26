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
        Schema::create('shipping_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('name_arabic')->nullable();
            $table->string('slug')->nullable();
            $table->string('description')->nullable();
            $table->string('description_arabic')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_classes');
    }
};
