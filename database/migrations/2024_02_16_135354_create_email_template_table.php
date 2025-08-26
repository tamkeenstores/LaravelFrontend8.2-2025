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
        Schema::create('email_template', function (Blueprint $table) {
            $table->id();
            $table->integer('purpose_template')->nullable();
            $table->text('notes')->nullable();
            $table->string('name')->nullable();
            $table->string('name_arabic')->nullable();
            $table->string('subject')->nullable();
            $table->string('subject_arabic')->nullable();
            $table->text('page_content')->nullable();
            $table->string('sms')->nullable();
            $table->string('sms_arabic')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_template');
    }
};
