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
        Schema::create('rule_restriction', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id');
            $table->integer('restriction_type');
            $table->integer('select_include_exclude')->nullable();
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
        Schema::dropIfExists('rule_restriction');
    }
};
