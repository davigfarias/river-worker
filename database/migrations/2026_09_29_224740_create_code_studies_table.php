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
        Schema::create('code_studies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('topic')->nullable();
            $table->foreignId('principle_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_studies');
    }
};
