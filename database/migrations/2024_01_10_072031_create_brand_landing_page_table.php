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
        Schema::create('brand_landing_page', function (Blueprint $table) {
            $table->id();
            $table->string('brand_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('brand_banner_link')->nullable();
            $table->integer('brand_banner_media')->nullable();
            $table->string('middle_banner_link')->nullable();
            $table->integer('middle_banner_media')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_landing_page');
    }
};
