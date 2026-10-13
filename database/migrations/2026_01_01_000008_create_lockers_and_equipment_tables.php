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
        Schema::create('lockers', function (Blueprint $table) {
            $table->id();
            $table->string('locker_number', 30)->unique(); // e.g. L-101
            $table->string('zone', 80)->default('General Floor'); // Men Zone, Women Zone, VIP
            $table->string('status', 30)->default('available'); // available, occupied, maintenance
            $table->foreignId('assigned_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('rental_fee_monthly', 8, 2)->default(15.00);
            $table->timestamps();

            $table->index(['zone', 'status']);
        });

        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('category', 50)->default('cardio'); // cardio, strength, free_weights, functional, recovery
            $table->string('serial_number', 100)->nullable();
            $table->string('location', 100)->default('Main Fitness Floor');
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('last_service_date')->nullable();
            $table->date('next_service_due')->nullable();
            $table->string('status', 30)->default('operational'); // operational, needs_service, under_maintenance, out_of_order
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['category', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('lockers');
    }
};
