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
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('full_name', 120);
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->jsonb('specialties')->nullable(); // PostgreSQL JSONB array of trainer skills
            $table->text('biography')->nullable();
            $table->decimal('hourly_rate', 8, 2)->default(40.00);
            $table->decimal('commission_rate_percentage', 5, 2)->default(20.00);
            $table->text('certifications')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index('is_available');
        });

        Schema::create('trainer_member_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('trainers')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('package_name', 120)->default('Standard PT Coaching');
            $table->integer('total_sessions')->default(10);
            $table->integer('completed_sessions')->default(0);
            $table->string('status', 30)->default('active'); // active, completed, paused, cancelled
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['trainer_id', 'status']);
            $table->index(['member_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_member_assignments');
        Schema::dropIfExists('trainers');
    }
};
