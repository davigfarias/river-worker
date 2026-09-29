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
        Schema::create('code_study_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_study_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('language')->default('php');
            $table->longText('snippet')->nullable();
            $table->longText('markdown')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_study_steps');
    }
};
