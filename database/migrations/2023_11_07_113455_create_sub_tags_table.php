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
        Schema::create('sub_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('tag_id');
            $table->integer('type')->nullable();
            $table->integer('image_media')->nullable();
            $table->string('name')->nullable();
            $table->string('name_arabic')->nullable();
            $table->integer('sort')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_tags');
    }
};
