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
        Schema::create('special_offer', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable();
            $table->integer('sorting')->nullable();
            $table->string('title')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('product_description')->nullable();
            $table->string('product_description_ar')->nullable();
            $table->integer('category_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_offer');
    }
};
