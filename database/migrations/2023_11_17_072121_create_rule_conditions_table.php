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
        Schema::create('rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id');
            $table->integer('condition_type');
            $table->integer('select_include_exclude')->default(0);
            $table->string('brand_id')->nullable();
            $table->string('sub_tag_id')->nullable();
            $table->string('category_id')->nullable();
            $table->string('product_id')->nullable();
            $table->integer('select_quantity')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('payment_method_id')->nullable();
            $table->string('shipping_method_id')->nullable();
            $table->integer('min_amount')->nullable(0);
            $table->integer('max_amount')->nullable(0);
            $table->string('email');
            $table->integer('phone_number');
            $table->integer('match_all')->default(0);
            $table->integer('match_any')->default(0);
            $table->date('date')->nullable;
            $table->string('start_time')->nullable;
            $table->string('end_time')->nullable;
            $table->date('dob');
            $table->integer('no_of_orders')->nullable;
            $table->string('city_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rule_conditions');
    }
};
