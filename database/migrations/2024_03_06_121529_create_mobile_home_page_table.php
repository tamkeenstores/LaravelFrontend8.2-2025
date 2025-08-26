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
        Schema::create('mobile_home_page', function (Blueprint $table) {
            $table->id();
            $table->string('cat_sec_heading')->nullable();
            $table->string('cat_sec_heading_arabic')->nullable();
            $table->string('cats_first_line')->nullable();
            $table->string('cats_second_line')->nullable();
            $table->string('cats_view_all_link')->nullable();
            $table->integer('cat_sec_status')->default(0);
            $table->string('first_pro_heading')->nullable();
            $table->string('first_pro_heading_arabic')->nullable();
            $table->string('first_products')->nullable();
            $table->string('first_pro_view_all_link')->nullable();
            $table->integer('first_pro_status')->default(0);
            $table->string('brands_heading')->nullable();
            $table->string('brands_heading_arabic')->nullable();
            $table->string('brands')->nullable();
            $table->string('brands_view_all_link')->nullable();
            $table->integer('brands_status')->default(0);
            $table->string('second_pro_heading')->nullable();
            $table->string('second_pro_heading_arabic')->nullable();
            $table->string('second_products')->nullable();
            $table->string('second_pro_view_all_link')->nullable();
            $table->integer('second_pro_status')->default(0);
            $table->string('images_heading')->nullable();
            $table->string('images_heading_arabic')->nullable();
            $table->integer('images_status')->default(0);
            $table->text('first_text_editor_data')->nullable();
            $table->string('third_pro_heading')->nullable();
            $table->string('third_pro_heading_arabic')->nullable();
            $table->string('third_products')->nullable();
            $table->string('third_pro_view_all_link')->nullable();
            $table->integer('third_pro_status')->default(0);
            $table->text('second_text_editor_data')->nullable();
            $table->string('services_heading')->nullable();
            $table->string('services_heading_arabic')->nullable();
            $table->text('third_text_editor_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_home_page');
    }
};
