<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Snapshot data
            $table->string('title');
            $table->string('slug');
            $table->enum('status', ['draft', 'published', 'scheduled', 'private']);
            $table->json('blocks');
            $table->json('seo')->nullable();

            // Revision metadata
            $table->string('change_summary')->nullable();
            $table->boolean('is_autosave')->default(false);

            $table->timestamps();

            $table->index(['page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
    }
};
