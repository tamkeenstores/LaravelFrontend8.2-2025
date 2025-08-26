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
        Schema::create('coupon', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_code');
            $table->text('description')->nullable();
            $table->string('discount_devices')->nullable();
            $table->integer('discount_type');
            $table->integer('discount_amount')->nullable();
            $table->integer('max_cap_amount')->nullable();
            $table->integer('usage_limit_coupon')->nullable();
            $table->integer('usage_limit_user')->nullable();
            $table->integer('coupon_restriction_type')->nullable();
            $table->string('brand_id')->nullable();
            $table->string('sub_tag_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('category_id')->nullable();
            $table->integer('restriction_auto_apply')->nullable();
            $table->integer('disable_rule_coupon')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon');
    }
};
