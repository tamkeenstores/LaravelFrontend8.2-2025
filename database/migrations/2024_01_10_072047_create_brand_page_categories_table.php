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
        Schema::create('brand_page_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('brand_landing_id');
            $table->integer('category_id')->nullable();
            $table->string('section')->nullable();
            $table->integer('sorting')->nullable();
            $table->string('link')->nullable();
            $table->integer('feature_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_page_categories');
    }
};
