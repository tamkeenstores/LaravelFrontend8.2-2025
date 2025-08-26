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
        Schema::create('general_setting', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('title_arabic')->nullable();
            $table->integer('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('address_arabic')->nullable();
            $table->string('primary_colour')->nullable();
            $table->string('secondary_colour')->nullable();
            $table->integer('favicon_image')->nullable();
            $table->integer('logo_web_image')->nullable();
            $table->integer('logo_mob_image')->nullable();
            $table->integer('saudi_business_status')->default(0);
            $table->string('saudi_business_link')->nullable();
            $table->integer('ministry_commerce_status')->default(0);
            $table->string('ministry_commerce_link')->nullable();
            $table->integer('maroof_status')->default(0);
            $table->string('maroof_link')->nullable();
            $table->integer('ministry_zakat_status')->default(0);
            $table->string('ministry_zakat_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_setting');
    }
};
