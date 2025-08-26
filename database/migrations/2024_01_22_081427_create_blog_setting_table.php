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
        Schema::create('blog_setting', function (Blueprint $table) {
            $table->id();
            $table->text('meta_title_en')->nullable();
            $table->text('meta_title_ar')->nullable();
            $table->text('meta_tag_en')->nullable();
            $table->text('meta_tag_ar')->nullable();
            $table->text('meta_canonical_en')->nullable();
            $table->text('meta_canonical_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->integer('slider_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_setting');
    }
};
