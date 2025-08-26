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
        Schema::create('return_refund', function (Blueprint $table) {
            $table->id();
            $table->integer('return_no');
            $table->integer('order_id');
            $table->integer('user_id');
            $table->string('reason_return')->nullable();
            $table->string('issues')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_refund');
    }
};
