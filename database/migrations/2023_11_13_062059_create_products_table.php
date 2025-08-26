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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_arabic');
            $table->text('short_description')->nullable();
            $table->text('short_description_arabic')->nullable();
            $table->text('description')->nullable();
            $table->text('description_arabic')->nullable();
            $table->string('slug');
            $table->integer('price')->nullable();
            $table->integer('sale_price')->nullable();
            $table->integer('sort')->default(0);
            $table->integer('tax_status')->nullable();
            $table->string('tax_class')->nullable();
            $table->text('notes')->nullable();
            $table->string('sku')->nullable();
            $table->string('quantity')->default(0);
            $table->integer('in_stock')->default(0);
            $table->string('shipping_class')->nullable();
            $table->integer('related_type')->default(0);
            $table->string('related_brands')->nullable();
            $table->string('related_categories')->nullable();
            $table->string('custom_badge_en')->nullable();
            $table->string('custom_badge_ar')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_ar')->nullable();
            $table->string('meta_tag_en')->nullable();
            $table->string('meta_tag_ar')->nullable();
            $table->string('meta_canonical_en')->nullable();
            $table->string('meta_canonical_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->string('brands')->nullable();
            $table->integer('best_seller')->default(0);
            $table->integer('free_gift')->default(0);
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
