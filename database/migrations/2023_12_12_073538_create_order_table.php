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
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('shipping_id')->nullable();
            $table->integer('billing_id')->nullable();
            $table->integer('coupon_id')->nullable();
            $table->string('coupon_code')->nullable();
            $table->string('subtotal');
            $table->string('tax')->nullable();
            $table->string('shipping')->nullable();
            $table->string('discount')->nullable();
            $table->string('total');
            $table->string('status')->default(0);
            $table->string('paymentmethod')->nullable();
            $table->string('paymentid')->nullable();
            $table->string('shippingMethod')->nullable();
            $table->string('note')->nullable();
            $table->string('include_tax')->nullable();
            $table->text('fees')->nullable();
            $table->string('discount_rule')->nullable();
            $table->string('discount_rule_id')->nullable();
            $table->string('discount_rule_bulk_id')->nullable();
            $table->integer('vat_discount')->default(0);
            $table->string('vat_discount_amount')->nullable();
            $table->integer('express_option_id')->nullable();
            $table->integer('express_option_price')->nullable();
            $table->string('express_option_label')->nullable();
            $table->string('lang')->nullable();
            $table->string('door_step_amount')->nullable();
            $table->string('cod_additional_charges')->nullable();
            $table->integer('erp_status')->default(0);
            $table->integer('madac_id')->nullable();
            $table->integer('shipping_carrier')->nullable();
            $table->integer('product_pickup_city')->nullable();
            $table->string('userDevice')->nullable();
            $table->string('token')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};
