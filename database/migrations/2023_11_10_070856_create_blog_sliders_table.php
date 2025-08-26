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
        Schema::create('blog_sliders', function (Blueprint $table) {
            $table->id();
            $table->text('sliders')->nullable();
            $table->string('slider_image_one')->nullable();
            $table->string('url_one')->nullable();
            $table->string('slider_image_two')->nullable();
            $table->string('url_two')->nullable();
            $table->string('slider_image_three')->nullable();
            $table->string('url_three')->nullable();
            $table->string('slider_image_four')->nullable();
            $table->string('url_four')->nullable();
            $table->string('slider_image_five')->nullable();
            $table->string('url_five')->nullable();
            $table->string('slider_image_six')->nullable();
            $table->string('url_six')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_sliders');
    }
};
