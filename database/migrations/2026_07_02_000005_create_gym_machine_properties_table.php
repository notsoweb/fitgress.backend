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
        Schema::create('gym_machine_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('gym_machines')->cascadeOnDelete();
            $table->string('name');
            $table->string('value')->nullable();
            $table->string('unit')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['machine_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_machine_properties');
    }
};
