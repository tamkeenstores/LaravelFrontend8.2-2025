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
        Schema::create('express_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_arabic');
            $table->integer('price')->nullable();
            $table->integer('num_of_days')->nullable();
            $table->integer('type')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('express_deliveries');
    }
};
