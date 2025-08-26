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
        Schema::create('loyalty_restriction', function (Blueprint $table) {
            $table->id();
            $table->integer('loyalty_id');
            $table->integer('restriction_type');
            $table->string('brand_id')->nullable();
            $table->string('sub_tag_id')->nullable();
            $table->string('category_id')->nullable();
            $table->string('product_id')->nullable();
            $table->integer('extra_reward_points')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_restriction');
    }
};
