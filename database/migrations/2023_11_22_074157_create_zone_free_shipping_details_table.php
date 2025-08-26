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
        Schema::create('zone_free_shipping_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_shipping_id');
            $table->integer('type');
            $table->string('minimum_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_free_shipping_details');
    }
};
