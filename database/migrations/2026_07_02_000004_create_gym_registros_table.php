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
        Schema::create('gym_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('gym_machines')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('gym_plans')->nullOnDelete();
            $table->unsignedSmallInteger('series');
            $table->unsignedSmallInteger('reps');
            $table->decimal('weight', 8, 3)->nullable();
            $table->dateTime('performed_at');
            $table->timestamps();

            $table->index(['user_id', 'performed_at']);
            $table->index('machine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_registros');
    }
};
