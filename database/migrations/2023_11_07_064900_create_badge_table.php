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
        Schema::create('badge', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('title_arabic')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->integer('badge_type')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('discount_arabic')->nullable();
            $table->integer('status')->default(0);
            $table->integer('for_web')->default(0);
            $table->integer('for_app')->default(0);
            $table->integer('image_media')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge');
    }
};
