<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Glitches de IA saíram do escopo: a tabela nunca chegou a ter uso.
     */
    public function up(): void
    {
        Schema::dropIfExists('work_issues');
    }

    public function down(): void
    {
        Schema::create('work_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_item_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->string('origin')->default('ai_glitch');
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });
    }
};
