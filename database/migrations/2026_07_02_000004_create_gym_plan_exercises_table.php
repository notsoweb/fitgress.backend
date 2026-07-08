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
        Schema::create('gym_plan_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('gym_plans')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('gym_exercises')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);

            $table->unique(['plan_id', 'exercise_id']);
            $table->index(['plan_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_plan_exercises');
    }
};
