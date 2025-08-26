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
        Schema::create('internal_order', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->integer('ticket_id');
            $table->integer('status')->nullable();
            $table->string('address');
            $table->integer('order_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_order');
    }
};
