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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('cdn_id');
            $table->string('thumbnail')->nullable();
            $table->string('extrasmall')->nullable();
            $table->string('small')->nullable();
            $table->string('medium')->nullable();
            $table->string('large')->nullable();
            $table->string('productimages')->nullable();
            $table->string('productimagegallery')->nullable();
            $table->string('file_url')->nullable();
            $table->integer('uploaded_by')->nullable();
            $table->string('title')->nullable();
            $table->string('title_arabic')->nullable();
            $table->string('alt')->nullable();
            $table->string('alt_arabic')->nullable();
            $table->string('details')->nullable();
            $table->integer('mobile')->default(0);
            $table->integer('desktop')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};