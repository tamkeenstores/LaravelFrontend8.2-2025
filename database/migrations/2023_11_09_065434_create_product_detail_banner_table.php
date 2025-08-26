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
        Schema::create('product_detail_banner', function (Blueprint $table) {
            $table->id();
            $table->string('start_date');
            $table->string('end_date');
            $table->integer('type');
            $table->integer('status')->default(0);
            $table->integer('for_web')->default(0);
            $table->integer('for_app')->default(0);
            $table->integer('image_en_media')->nullable();
            $table->integer('image_ar_media')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_detail_banner');
    }
};
