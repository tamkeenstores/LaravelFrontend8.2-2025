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
        Schema::create('product_review', function (Blueprint $table) {
            $table->id();
            $table->integer('orderdetail_id')->nullable();
            $table->string('product_sku');
            $table->integer('rating')->default(0);
            $table->string('title');
            $table->text('review')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('anonymous')->default(0);
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_review');
    }
};
