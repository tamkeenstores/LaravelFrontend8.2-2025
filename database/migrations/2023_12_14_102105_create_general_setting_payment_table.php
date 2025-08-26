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
        Schema::create('general_setting_payment', function (Blueprint $table) {
            $table->id();
            $table->integer('generalsetting_id');
            $table->integer('hyperpay_status')->default(0);
            $table->integer('hyperpay_exclude_type')->nullable();
            $table->integer('hyperpay_brand_id')->nullable();
            $table->integer('hyperpay_product_id')->nullable();
            $table->integer('hyperpay_sub_tag_id')->nullable();
            $table->integer('hyperpay_category_id')->nullable();
            $table->integer('hyperpay_min_value')->nullable();
            $table->integer('hyperpay_max_value')->nullable();
            $table->integer('applepay_status')->default(0);
            $table->integer('applepay_exclude_type')->nullable();
            $table->integer('applepay_brand_id')->nullable();
            $table->integer('applepay_product_id')->nullable();
            $table->integer('applepay_sub_tag_id')->nullable();
            $table->integer('applepay_category_id')->nullable();
            $table->integer('applepay_min_value')->nullable();
            $table->integer('applepay_max_value')->nullable();
            $table->integer('tasheel_status')->default(0);
            $table->integer('tasheel_exclude_type')->nullable();
            $table->integer('tasheel_brand_id')->nullable();
            $table->integer('tasheel_product_id')->nullable();
            $table->integer('tasheel_sub_tag_id')->nullable();
            $table->integer('tasheel_category_id')->nullable();
            $table->integer('tasheel_min_value')->nullable();
            $table->integer('tasheel_max_value')->nullable();
            $table->integer('tabby_status')->default(0);
            $table->integer('tabby_exclude_type')->nullable();
            $table->integer('tabby_brand_id')->nullable();
            $table->integer('tabby_product_id')->nullable();
            $table->integer('tabby_sub_tag_id')->nullable();
            $table->integer('tabby_category_id')->nullable();
            $table->integer('tabby_min_value')->nullable();
            $table->integer('tabby_max_value')->nullable();
            $table->integer('tamara_status')->default(0);
            $table->integer('tamara_exclude_type')->nullable();
            $table->integer('tamara_brand_id')->nullable();
            $table->integer('tamara_product_id')->nullable();
            $table->integer('tamara_sub_tag_id')->nullable();
            $table->integer('tamara_category_id')->nullable();
            $table->integer('tamara_min_value')->nullable();
            $table->integer('tamara_max_value')->nullable();
            $table->integer('cod_status')->default(0);
            $table->integer('cod_exclude_type')->nullable();
            $table->integer('cod_brand_id')->nullable();
            $table->integer('cod_product_id')->nullable();
            $table->integer('cod_sub_tag_id')->nullable();
            $table->integer('cod_category_id')->nullable();
            $table->integer('cod_min_value')->nullable();
            $table->integer('cod_max_value')->nullable();
            $table->integer('cod_city_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_setting_payment');
    }
};
