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
        Schema::create('wallet_setting', function (Blueprint $table) {
            $table->id();
            $table->integer('new_user')->default(0);
            $table->integer('new_user_amount')->default(0);
            $table->string('new_user_device')->nullable();
            $table->integer('all_user')->default(0);
            $table->integer('all_user_amount')->default(0);
            $table->string('all_user_device')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_setting');
    }
};
