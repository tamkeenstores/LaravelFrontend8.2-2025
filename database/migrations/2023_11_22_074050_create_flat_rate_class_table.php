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
        Schema::create('flat_rate_class', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_shipping_id');
            $table->unsignedBigInteger('class_id');
            $table->integer('cost')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flat_rate_class');
    }
};
