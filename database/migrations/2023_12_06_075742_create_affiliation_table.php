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
        Schema::create('affiliation', function (Blueprint $table) {
            $table->id();
            $table->integer('rules_type')->nullable();
            $table->string('rules_id')->nullable();
            $table->string('coupon_id')->nullable();
            $table->string('free_gifts_id')->nullable();
            $table->string('gift_voucher_id')->nullable();
            $table->string('loyalty_id')->nullable();
            $table->integer('disable_rules')->nullable();
            $table->text('notes')->nullable();
            $table->string('slug_code')->nullable();
            $table->integer('redirect_type')->nullable();
            $table->string('pages_id')->nullable();
            $table->string('brand_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('sub_tag_id')->nullable();
            $table->string('category_id')->nullable();
            $table->string('custom_link')->nullable();
            $table->string('discount_devices')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('specific_users_id')->nullable();
            $table->integer('status')->default(0);
            $table->integer('disable_conditions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliation');
    }
};
