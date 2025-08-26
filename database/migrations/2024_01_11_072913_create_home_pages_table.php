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
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();
            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_ar')->nullable();
            $table->string('meta_description_en')->nullable();
            $table->string('meta_description_ar')->nullable();
            $table->string('categories_top')->nullable();
            $table->string('categories_top_status')->nullable();
            $table->string('brands_middle')->nullable();
            $table->string('brands_middle_status')->nullable();
            $table->string('products_first')->nullable();
            $table->string('products_first_status')->nullable();
            $table->string('products_second')->nullable();
            $table->string('products_second_status')->nullable();
            $table->string('products_third')->nullable();
            $table->string('products_third_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_pages');
    }
};
