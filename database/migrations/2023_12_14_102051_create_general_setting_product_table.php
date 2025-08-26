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
        Schema::create('general_setting_product', function (Blueprint $table) {
            $table->id();
            $table->integer('generalsetting_id');
            $table->integer('catalog_badge_status')->default(0);
            $table->integer('product_badge_status')->default(0);
            $table->integer('discount_type')->nullable();
            $table->integer('hot')->default(0);
            $table->integer('new')->default(0);
            $table->integer('sales')->default(0);
            $table->integer('out_of_stock')->default(0);
            $table->integer('low_in_stock')->default(0);
            $table->integer('selling_out_fast')->default(0);
            $table->string('hot_badge')->nullable();
            $table->string('hot_badge_arabic')->nullable();
            $table->string('hot_badge_colour')->nullable();
            $table->string('new_badge')->nullable();
            $table->string('new_badge_arabic')->nullable();
            $table->integer('new_badge_days')->default(0);
            $table->string('new_badge_colour')->nullable();
            $table->string('low_in_stock_badge')->nullable();
            $table->string('low_in_stock_badge_arabic')->nullable();
            $table->string('low_in_stock_badge_colour')->nullable();
            $table->string('selling_out_fast_badge')->nullable();
            $table->string('selling_out_fast_badge_arabic')->nullable();
            $table->string('selling_out_fast_badge_colour')->nullable();
            $table->string('out_of_stock_badge')->nullable();
            $table->string('out_of_stock_badge_arabic')->nullable();
            $table->string('out_of_stock_badge_colour')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_setting_product');
    }
};
