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
        Schema::create('loyalty_setting_desktop', function (Blueprint $table) {
            $table->id();
            $table->integer('loyaltysetting_id');
            $table->integer('min_order_value')->nullable();
            $table->integer('max_order_value')->nullable();
            $table->integer('reward_points')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_setting_desktop');
    }
};
