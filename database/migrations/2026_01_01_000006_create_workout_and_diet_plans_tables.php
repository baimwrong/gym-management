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
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnDelete();
            $table->string('difficulty_level', 30)->default('intermediate'); // beginner, intermediate, advanced
            $table->integer('days_per_week')->default(4);
            $table->jsonb('routine_schedule')->nullable(); // PostgreSQL JSONB structure
            $table->boolean('is_template')->default(false);
            $table->timestamps();

            $table->index(['member_id', 'is_template']);
        });

        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnDelete();
            $table->integer('target_calories')->default(2500);
            $table->integer('protein_grams')->default(180);
            $table->integer('carbs_grams')->default(250);
            $table->integer('fats_grams')->default(70);
            $table->jsonb('meals')->nullable(); // PostgreSQL JSONB breakdown
            $table->boolean('is_template')->default(false);
            $table->timestamps();

            $table->index(['member_id', 'is_template']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_plans');
        Schema::dropIfExists('workout_plans');
    }
};
