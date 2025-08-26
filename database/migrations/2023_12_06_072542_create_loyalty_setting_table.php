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
        Schema::create('loyalty_setting', function (Blueprint $table) {
            $table->id();
            $table->text('notes_desktop')->nullable();
            $table->text('notes_mobile')->nullable();
            $table->integer('extra_reward_newuser')->nullable();
            $table->integer('reward_newuser_amount')->nullable();
            $table->integer('extra_reward_freeshipping')->nullable();
            $table->integer('reward_freeshipping_amount')->nullable();
            $table->string('discount_devices')->nullable();
            $table->integer('min_order_value')->nullable();
            $table->integer('reward_points')->nullable();
            $table->integer('fixed_amount_mobile')->nullable();
            $table->integer('fixed_amount_website')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_setting');
    }
};
