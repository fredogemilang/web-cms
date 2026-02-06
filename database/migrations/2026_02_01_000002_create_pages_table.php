<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();

            // Hierarchical support
            $table->foreignId('parent_id')->nullable()
                ->constrained('pages')->onDelete('set null');
            $table->unsignedInteger('menu_order')->default(0);

            // Status & Publishing
            $table->enum('status', ['draft', 'published', 'scheduled', 'private'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');

            // Template & Layout
            $table->string('template')->default('default');

            // Featured Image
            $table->string('featured_image')->nullable();

            // SEO Metadata (JSON column)
            $table->json('seo')->nullable();

            // Settings (JSON column)
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['parent_id', 'menu_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
