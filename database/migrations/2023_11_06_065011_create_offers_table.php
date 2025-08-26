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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_arabic');
            $table->string('subtitle');
            $table->string('subtitle_arabic');
            $table->string('button_title')->nullable();
            $table->string('button_title_arabic')->nullable();
            $table->string('button_slug')->nullable();
            $table->string('button_id')->nullable();
            $table->string('section_id')->nullable();
            $table->integer('viewtype')->default(0);
            $table->integer('type');
            $table->string('product_ids')->nullable();
            $table->string('brand_ids')->nullable();
            $table->string('category_ids')->nullable();
            $table->string('boxcolor')->nullable();
            $table->string('textcolor')->nullable();
            $table->string('btncolor')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
