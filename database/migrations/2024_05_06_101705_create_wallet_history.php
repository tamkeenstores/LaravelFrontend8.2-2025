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
        Schema::create('wallet_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('order_id')->nullable();
            $table->string('type')->nullable();
            $table->string('amount')->nullable();
            $table->string('description')->nullable();
            $table->string('description_arabic')->nullable();
            $table->string('wallet_type')->nullable();
            $table->string('title')->nullable();
            $table->string('title_arabic')->nullable();
            $table->integer('current_amount')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_histories');
    }
};
