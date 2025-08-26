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
        Schema::create('shoppingcart_nextjs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->longtext('cartdata');
            $table->integer('firstemail')->default(0);
            $table->integer('secondemail')->default(0);
            $table->integer('thirdemail')->default(0);
            $table->integer('fourthemail')->default(0);
            $table->integer('affiliate_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoppingcart_nextjs');
    }
};
