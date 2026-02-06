<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tier_id')->constrained('membership_tiers')->cascadeOnDelete();
            
            // Membership Period
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null for lifetime
            
            // Status
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            
            // Payment Info
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->dateTime('payment_date')->nullable();
            
            // Auto Renewal
            $table->boolean('auto_renew')->default(false);
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // For custom fields from form
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('end_date');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
