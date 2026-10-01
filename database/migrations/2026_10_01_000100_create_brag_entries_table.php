<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brag_entries', function (Blueprint $table) {
            $table->id();
            $table->string('goal');
            $table->string('deadline')->nullable();
            $table->string('project')->nullable();
            $table->text('contribution')->nullable();
            $table->text('stakeholders')->nullable();
            $table->text('impact_changed')->nullable();
            $table->text('impact_easier')->nullable();
            $table->text('impact_faster')->nullable();
            $table->text('impact_clearer')->nullable();
            $table->text('impact_problem_gone')->nullable();
            $table->text('trainings')->nullable();
            $table->text('feedbacks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brag_entries');
    }
};
