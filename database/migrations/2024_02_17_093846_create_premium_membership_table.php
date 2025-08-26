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
        Schema::create('premium_membership', function (Blueprint $table) {
            $table->id();
            $table->integer('premium_id');
            $table->string('name')->nullable();
            $table->string('name_arabic')->nullable();
            $table->integer('restriction_type');
            $table->string('brand_id')->nullable();
            $table->string('sub_tag_id')->nullable();
            $table->string('category_id')->nullable();
            $table->string('product_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_membership');
    }
};
