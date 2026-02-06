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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('filename'); // Sanitized filename on disk
            $table->string('original_filename'); // Original uploaded filename
            $table->string('mime_type');
            $table->string('file_extension', 10);
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->string('path'); // Relative path to original file
            $table->string('webp_path')->nullable(); // Relative path to WebP version
            $table->unsignedInteger('width')->nullable(); // Image width
            $table->unsignedInteger('height')->nullable(); // Image height
            $table->text('alt_text')->nullable(); // Alternative text for images
            $table->string('title')->nullable(); // Media title
            $table->text('description')->nullable(); // Media description
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes for better query performance
            $table->index('mime_type');
            $table->index('uploaded_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
