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
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_arabic');
            $table->text('notes')->nullable();
            $table->integer('discount_type');
            $table->integer('usage_limit')->nullable();
            $table->integer('bogo_discount_type');
            $table->integer('bogo_status')->nullable();
            $table->string('cart_discount_depend')->nullable();
            $table->integer('cart_fixed_amount')->nullable();
            $table->integer('cart_maxcap_amount')->nullable();
            $table->string('discount_devices')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};
