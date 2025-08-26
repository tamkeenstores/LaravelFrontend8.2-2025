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
        Schema::create('gift_voucher', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_arabic');
            $table->text('description')->nullable();
            $table->string('discount_devices')->nullable();
            $table->integer('discount_type');
            $table->integer('discount_amount')->nullable();
            $table->integer('max_cap_amount')->nullable();
            $table->integer('usage_limit_voucher')->nullable();
            $table->integer('usage_limit_user')->nullable();
            $table->integer('voucher_restriction_type')->nullable();
            $table->string('restriction_brand_id')->nullable();
            $table->string('restriction_sub_tag_id')->nullable();
            $table->string('restriction_product_id')->nullable();
            $table->string('restriction_category_id')->nullable();
            $table->integer('restriction_auto_apply')->nullable();
            $table->integer('voucher_applied_type')->nullable();
            $table->string('applied_brand_id')->nullable();
            $table->string('applied_sub_tag_id')->nullable();
            $table->string('applied_product_id')->nullable();
            $table->string('applied_category_id')->nullable();
            $table->integer('voucher_disable_rules')->nullable();
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
        Schema::dropIfExists('gift_voucher');
    }
};
