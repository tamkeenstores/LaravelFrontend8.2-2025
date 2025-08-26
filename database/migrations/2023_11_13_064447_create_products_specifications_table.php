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
        Schema::create('products_specifications', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('specs_en')->nullable();
            $table->string('value_en')->nullable();
            $table->string('specs_ar')->nullable();
            $table->string('value_ar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_specifications');
    }
};
