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
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->integer('position')->nullable();
            $table->integer('pages')->nullable();
            $table->string('popup_devices')->nullable();
            $table->integer('type')->nullable();
            $table->string('time')->nullable();
            $table->text('text')->nullable();
            $table->integer('image')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
