<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            
            // Event Details
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->string('timezone')->default('Asia/Jakarta');
            
            // Location
            $table->string('location')->nullable();
            $table->string('location_address')->nullable();
            $table->string('location_url')->nullable(); // Google Maps link
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Event Type & Category
            $table->enum('event_type', ['online', 'offline', 'hybrid'])->default('offline');
            $table->string('online_meeting_url')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('event_categories')->nullOnDelete();
            
            // Registration
            $table->boolean('requires_registration')->default(false);
            $table->integer('max_participants')->nullable();
            $table->integer('registered_count')->default(0);
            $table->dateTime('registration_deadline')->nullable();
            
            // Media
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('gallery_images')->nullable(); // Array of media IDs
            
            // Status & Publishing
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            
            // Additional Settings
            $table->json('settings')->nullable(); // For custom fields, organizer info, etc.
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('start_date');
            $table->index('status');
            $table->index('event_type');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
