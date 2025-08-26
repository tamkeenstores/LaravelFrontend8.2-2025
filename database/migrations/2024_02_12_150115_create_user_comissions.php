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
        Schema::create('user_comissions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('affiliation_id')->nullable();
            $table->string('title')->nullable();
            $table->string('title_arabic')->nullable();
            $table->integer('disable_type')->nullable();
            $table->integer('rules_type')->nullable();
            $table->integer('rules_id')->nullable();
            $table->integer('coupon_id')->nullable();
            $table->string('slug_code')->nullable();
            $table->string('value')->nullable();
            $table->string('order_no')->nullable();
            $table->string('notes')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_comissions');
    }
};
