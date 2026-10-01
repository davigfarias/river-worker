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
        Schema::create('work_item_commits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_item_id')->constrained()->cascadeOnDelete();
            $table->string('sha', 40);
            $table->string('message', 500);
            $table->string('author')->nullable();
            $table->timestamp('committed_at')->nullable();
            $table->string('url', 2048);
            $table->timestamps();

            $table->unique(['work_item_id', 'sha']);
            $table->index('sha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_item_commits');
    }
};
