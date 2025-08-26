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
        Schema::create('product_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('question')->nullable();
            $table->string('question_arabic')->nullable();
            $table->string('answer')->nullable();
            $table->string('answer_arabic')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_faqs');
    }
};
