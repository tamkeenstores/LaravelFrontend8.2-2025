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
        Schema::create('product_specs_details', function (Blueprint $table) {
            $table->id();
            $table->integer('specs_id');
            $table->string('specs_en')->nullable();
            $table->string('value_en')->nullable();
            $table->string('specs_ar')->nullable();
            $table->string('value_ar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specs_details');
    }
};
