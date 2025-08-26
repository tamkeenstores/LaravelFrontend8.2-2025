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
        Schema::create('bogo_discount', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id');
            $table->integer('min_quantity')->nullable();
            $table->integer('max_quantity')->nullable();
            $table->string('products_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('discount_depend')->nullable();
            $table->integer('fixed_amount')->nullable();
            $table->integer('recursive')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bogo_discount');
    }
};
