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
        Schema::create('segmentation_condition', function (Blueprint $table) {
            $table->id();
            $table->integer('segment_id');
            $table->integer('condition_type');
            $table->string('coupon_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->integer('orders_count')->nullable();
            $table->integer('last_order_days')->nullable();
            $table->integer('first_order_days')->nullable();
            $table->integer('order_min_value')->nullable();
            $table->integer('order_max_value')->nullable();
            $table->string('user_gender')->nullable();
            $table->string('user_dob')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('shipping_cities')->nullable();
            $table->string('shipping_region')->nullable();
            $table->integer('cart_count')->nullable();
            $table->string('wishlist_brands')->nullable();
            $table->string('wishlist_products')->nullable();
            $table->string('wishlist_cats')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segmentation_condition');
    }
};
