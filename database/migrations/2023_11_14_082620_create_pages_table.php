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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('meta_title')->nullable();
            $table->string('meta_title_arabic')->nullable();
            $table->string('meta_tag')->nullable();
            $table->string('meta_tag_arabic')->nullable();
            $table->string('meta_canonical')->nullable();
            $table->string('meta_canonical_arabic')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_description_arabic')->nullable();
            $table->text('description')->nullable();
            $table->text('description_arabic')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
