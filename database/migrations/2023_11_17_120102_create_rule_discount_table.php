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
        Schema::create('rule_discount', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id');
            $table->integer('discount_type');
            $table->integer('add_cart_page')->nullable();
            $table->integer('add_cheapest')->nullable();
            $table->integer('add_highest')->nullable();
            $table->integer('min_quantity')->nullable();
            $table->integer('max_quantity')->nullable();
            $table->string('products_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('discount_depend')->nullable();
            $table->integer('fixed_amount')->nullable();
            $table->integer('recursive')->nullable();
            $table->integer('product')->nullable();
            $table->integer('product_fixed_amount')->nullable();
            $table->integer('bulk_discount_type')->nullable();
            $table->integer('discount_amount')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rule_discount');
    }
};
