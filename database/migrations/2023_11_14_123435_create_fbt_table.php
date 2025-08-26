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
        Schema::create('fbt', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_arabic');
            $table->text('notes')->nullable();
            $table->integer('fbt_type');
            $table->integer('show_on_product')->default(0);
            $table->integer('show_on_cart')->default(0);
            $table->integer('multi_quantity')->default(0);
            $table->string('include_cities')->nullable();
            $table->string('exclude_cities')->nullable();
            $table->integer('cities_restriction')->default(0);
            $table->integer('discount_type');
            $table->integer('amount_type');
            $table->integer('restriction_pages');
            $table->integer('min_amount')->nullable();
            $table->integer('max_amount')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_user_limit')->nullable();
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
        Schema::dropIfExists('fbt');
    }
};
