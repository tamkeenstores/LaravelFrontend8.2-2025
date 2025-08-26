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
        Schema::create('footer_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_name')->nullable();
            $table->string('page_link')->nullable();
            $table->text('notes')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_ar')->nullable();
            $table->string('meta_tag_en')->nullable();
            $table->string('meta_tag_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->text('page_content_en')->nullable();
            $table->text('page_content_ar')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footer_pages');
    }
};
