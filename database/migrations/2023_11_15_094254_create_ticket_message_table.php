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
        Schema::create('ticket_message', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('ticket_no')->nullable();
            $table->integer('department')->nullable();
            $table->integer('priority')->nullable();
            $table->string('related_service')->nullable();
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->string('document')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_message');
    }
};
