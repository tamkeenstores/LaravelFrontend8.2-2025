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
        Schema::create('app_sliders', function (Blueprint $table) {
            $table->id();
            $table->integer('popup_type');
            $table->integer('image')->nullable();
            $table->integer('image_arabic')->nullable();
            $table->string('title')->nullable();
            $table->string('title_arabic')->nullable();
            $table->string('slider_name')->nullable();
            $table->string('slider_name_arabic')->nullable();
            $table->integer('sorting')->nullable();
            $table->integer('type')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('page')->nullable();
            $table->string('btn_name')->nullable();
            $table->string('btn_name_arabic')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_sliders');
    }
};
