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
        // Drop old tables
        Schema::dropIfExists('membership_benefits');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('membership_tiers');

        // Create simplified memberships table
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Status
            $table->enum('status', ['pending', 'active', 'rejected', 'suspended'])->default('pending');
            
            // Dates
            $table->date('joined_at')->nullable(); // When approved/activated
            
            // Additional Info
            $table->text('notes')->nullable(); // Admin notes
            $table->json('metadata')->nullable(); // Custom fields from registration form
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->unique('user_id'); // One membership per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
        
        // Recreate old structure if needed (optional)
        // Not implementing reverse as this is a major refactor
    }
};
