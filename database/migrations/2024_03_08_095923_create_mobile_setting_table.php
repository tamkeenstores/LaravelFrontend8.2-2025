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
        Schema::create('mobile_setting', function (Blueprint $table) {
            $table->id();
            $table->integer('image')->nullable();
            $table->integer('image_arabic')->nullable();
            $table->string('heading')->nullable();
            $table->string('heading_arabic')->nullable();
            $table->integer('project_sale_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_setting');
    }
};
