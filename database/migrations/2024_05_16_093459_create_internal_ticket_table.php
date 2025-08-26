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
        Schema::create('internal_ticket', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('emergency')->default(0);
            $table->integer('follow_up')->default(0);
            $table->integer('status')->default(0);
            $table->string('subject')->nullable();
            $table->text('details')->nullable();
            $table->integer('input_channel')->nullable();
            $table->integer('type')->nullable();
            $table->integer('department')->nullable();
            $table->integer('section')->nullable();
            $table->integer('branch')->nullable();
            $table->integer('branch_type')->nullable();
            $table->integer('assignee')->nullable();
            $table->integer('assignee_type')->nullable();
            $table->integer('purchased_from')->nullable();
            $table->integer('urgency')->nullable();
            $table->integer('impact')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('order_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_ticket');
    }
};
