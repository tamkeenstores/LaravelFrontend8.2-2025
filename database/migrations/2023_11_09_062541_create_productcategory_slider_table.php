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
        Schema::create('productcategory_slider', function (Blueprint $table) {
            $table->id();
            $table->integer('productcategory_id')->nullable();
            $table->integer('image_media')->nullable();
            $table->string('category_slug')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productcategory_slider');
    }
};
