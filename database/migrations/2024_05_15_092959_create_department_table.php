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
        Schema::create('department', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_arabic');
            $table->text('notes')->nullable();
            $table->integer('branch_status')->default(0);
            $table->integer('branch')->nullable();
            $table->integer('section_status')->default(0);
            $table->integer('manager_status')->default(0);
            $table->integer('manager')->nullable();
            $table->integer('supervisor_status')->default(0);
            $table->integer('supervisor')->nullable();
            $table->integer('team_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department');
    }
};
