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
        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('title_arabic')->nullable();
            $table->text('message')->nullable();
            $table->text('image')->nullable();
            $table->date('date')->nullable();
            $table->text('link')->nullable();
            $table->integer('for_web')->default(0);
            $table->integer('for_app')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
};
