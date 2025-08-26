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
        Schema::create('coupon_restriction', function (Blueprint $table) {
            $table->id();
            $table->integer('coupon_id');
            $table->integer('disabled_type')->nullable();
            $table->integer('select_include_exclude')->nullable();
            $table->string('rules_id')->nullable();
            $table->string('free_gifts_id')->nullable();
            $table->string('special_offers_id')->nullable();
            $table->string('discount_coupon_id')->nullable();
            $table->string('fbt_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_restriction');
    }
};
