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
        Schema::create('bulk_discount', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id');
            $table->integer('min_quantity')->nullable();
            $table->integer('max_quantity')->nullable();
            $table->integer('bulk_discount_type')->nullable();
            $table->integer('discount_amount')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_discount');
    }
};
