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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_arabic');
            $table->string('slug');
            $table->integer('sorting')->nullable();
            $table->string('description')->nullable();
            $table->string('description_arabic')->nullable();
            $table->integer('status')->default(0);
            $table->integer('show_as_popular')->default(0);
            $table->string('image')->nullable();
            $table->string('app_image')->nullable();
            $table->text('meta_title_en')->nullable();
            $table->text('meta_title_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->text('meta_tag_en')->nullable();
            $table->text('meta_tag_ar')->nullable();
            $table->text('meta_canonical_en')->nullable();
            $table->text('meta_canonical_ar')->nullable();
            $table->integer('brand_image_media')->nullable();
            $table->integer('brand_app_image_media')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
