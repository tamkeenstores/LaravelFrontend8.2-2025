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
        Schema::create('order_detail', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->text('product_image')->nullable();
            $table->string('unit_price')->nullable();
            $table->string('quantity')->nullable();
            $table->string('total')->nullable();
            $table->integer('enable_vat')->default(0);
            $table->integer('expressproduct')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_detail');
    }
};
