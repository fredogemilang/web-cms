<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tier_id')->constrained('membership_tiers')->cascadeOnDelete();
            $table->enum('benefit_type', ['discount', 'access', 'feature', 'other'])->default('feature');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('value')->nullable(); // For discount %, access level, etc.
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_benefits');
    }
};
