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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->integer('slider_type')->nullable();
            $table->string('alt')->nullable();
            $table->string('alt_ar')->nullable();
            $table->string('video_link_web')->nullable();
            $table->string('video_link_app')->nullable();
            $table->string('video_interval_web')->nullable();
            $table->string('video_interval_app')->nullable();
            $table->string('slider_devices')->nullable();
            $table->integer('redirection_type')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('sub_tag_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('custom_link')->nullable();
            $table->integer('sorting')->nullable();
            $table->integer('status')->default(0);
            $table->integer('image_web')->nullable();
            $table->integer('image_mobile')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
