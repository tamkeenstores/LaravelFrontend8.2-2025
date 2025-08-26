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
        Schema::create('blog', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('name_arabic')->nullable();
            $table->string('slug')->nullable();
            $table->text('content')->nullable();
            $table->text('content_arabic')->nullable();
            $table->text('description')->nullable();
            $table->text('description_arabic')->nullable();
            $table->integer('status')->default(0);
            $table->integer('image_media')->nullable();
            $table->integer('viewed_blog')->default(0);
            $table->text('meta_title_en')->nullable();
            $table->text('meta_title_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->text('meta_tag_en')->nullable();
            $table->text('meta_tag_ar')->nullable();
            $table->text('meta_canonical')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog');
    }
};
