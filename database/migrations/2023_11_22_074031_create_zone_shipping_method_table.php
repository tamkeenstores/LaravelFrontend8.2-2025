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
        Schema::create('zone_shipping_method', function (Blueprint $table) {
            $table->id();
            $table->integer('zone_id');
            $table->integer('type');
            $table->integer('cost')->nullable();
            $table->string('title')->nullable();
            $table->integer('tax_status')->default(0);
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_shipping_method');
    }
};
