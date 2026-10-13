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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('member_code', 30)->unique(); // e.g. GYM-2026-1001
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('gender', 20)->default('male'); // male, female, other
            $table->date('date_of_birth')->nullable();
            $table->string('email', 150)->nullable()->index();
            $table->string('phone', 30)->nullable()->index();
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('blood_type', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('medical_notes')->nullable();
            $table->jsonb('fitness_goals')->nullable(); // PostgreSQL JSONB array of goals
            $table->string('barcode_value', 100)->nullable()->index();
            $table->string('rfid_card_number', 100)->nullable()->index();
            $table->string('avatar')->nullable();
            $table->string('status', 30)->default('active'); // active, expired, frozen, inactive
            $table->date('joined_date')->default(now());
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('member_health_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->date('recorded_date');
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->decimal('body_fat_percentage', 4, 1)->nullable();
            $table->decimal('muscle_mass_kg', 4, 1)->nullable();
            $table->decimal('chest_cm', 5, 2)->nullable();
            $table->decimal('waist_cm', 5, 2)->nullable();
            $table->decimal('arms_cm', 5, 2)->nullable();
            $table->decimal('thighs_cm', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['member_id', 'recorded_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_health_metrics');
        Schema::dropIfExists('members');
    }
};
