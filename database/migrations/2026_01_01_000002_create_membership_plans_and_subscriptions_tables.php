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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('duration_type', 20)->default('months'); // days, months, years
            $table->integer('duration_count')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('signup_fee', 10, 2)->default(0.00);
            $table->boolean('includes_classes')->default(true);
            $table->integer('includes_pt_sessions')->default(0);
            $table->integer('max_freeze_days')->default(30);
            $table->jsonb('features')->nullable(); // PostgreSQL JSONB array of perks
            $table->string('badge_color', 30)->default('primary');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('member_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('membership_plans')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('active'); // active, expired, cancelled, frozen, pending
            $table->decimal('price_paid', 10, 2);
            $table->integer('remaining_pt_sessions')->default(0);
            $table->boolean('is_auto_renew')->default(false);
            $table->timestamp('frozen_at')->nullable();
            $table->date('unfreeze_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['end_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_subscriptions');
        Schema::dropIfExists('membership_plans');
    }
};
