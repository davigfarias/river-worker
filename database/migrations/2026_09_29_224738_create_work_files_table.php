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
        Schema::create('work_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_item_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->text('reason_notes')->nullable();
            $table->boolean('is_reviewed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_files');
    }
};
