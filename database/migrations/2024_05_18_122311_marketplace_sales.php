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
        Schema::create('marketplacesales', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->integer('amazon');
            $table->integer('carefour');
            $table->integer('homzmart');
            $table->integer('noon');
            $table->integer('centerpoint');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companytypes');
    }
};
