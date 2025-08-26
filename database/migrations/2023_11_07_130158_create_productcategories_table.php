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
        Schema::create('productcategories', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->integer('sort')->nullable();
            $table->string('name')->nullable();
            $table->string('name_arabic')->nullable();
            $table->text('description')->nullable();
            $table->text('description_arabic')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_ar')->nullable();
            $table->string('meta_tag_en')->nullable();
            $table->string('meta_tag_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->string('meta_canonical_en')->nullable();
            $table->string('meta_canonical_ar')->nullable();
            $table->integer('status')->default(0);
            $table->integer('web_image_media')->nullable();
            $table->integer('mobile_image_media')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('menu')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productcategories');
    }
};
