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
        Schema::create('shipping_location', function (Blueprint $table) {
            $table->id();
            $table->integer('region')->nullable();
            $table->integer('city')->nullable();
            $table->string('samsa')->nullable();
            $table->string('aramex')->nullable();
            $table->string('naqeel')->nullable();
            $table->string('flow')->nullable();
            $table->integer('country')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_location');
    }
};
