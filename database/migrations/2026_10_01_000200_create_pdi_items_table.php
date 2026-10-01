<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdi_items', function (Blueprint $table) {
            $table->id();
            $table->string('objective');
            $table->text('current_situation')->nullable();
            $table->text('action')->nullable();
            $table->text('measurement')->nullable();
            $table->string('deadline')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdi_items');
    }
};
