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
        Schema::create('tax', function (Blueprint $table) {
            $table->id();
            $table->integer('class_id');
            $table->integer('country_code')->default(0);
            $table->integer('state_code')->default(0);
            $table->integer('postcode_zip')->default(0);
            $table->integer('city')->default(0);
            $table->integer('rate')->nullable();
            $table->string('tax_name')->nullable();
            $table->string('tax_name_arabic')->nullable();
            $table->integer('priority')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax');
    }
};
