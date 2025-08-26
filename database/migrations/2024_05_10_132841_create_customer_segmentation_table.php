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
        Schema::create('customer_segmentation', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_arabic');
            $table->text('notes')->nullable();
            $table->integer('status')->default(0);
            $table->integer('new_user')->default(0);
            $table->string('email_template_type')->nullable();
            $table->string('wp_template_type')->nullable();
            $table->text('sms')->nullable();
            $table->integer('sms_status')->default(0);
            $table->text('sms_arabic')->nullable();
            $table->integer('sms_arabic_status')->default(0);
            $table->integer('send_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_segmentation');
    }
};
