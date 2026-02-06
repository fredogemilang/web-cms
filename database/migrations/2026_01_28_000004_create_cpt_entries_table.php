<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This creates a universal content entries table that stores all CPT entries
     * with a flexible meta storage approach (JSON column for meta values)
     */
    public function up(): void
    {
        // Main entries table for all CPT content
        Schema::create('cpt_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_type_id')->constrained('custom_post_types')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->index();
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('cpt_entries')->onDelete('set null');
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->json('meta')->nullable(); // Store all meta field values as JSON
            $table->unsignedInteger('menu_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['post_type_id', 'slug']);
            $table->index(['post_type_id', 'status', 'published_at']);
        });

        // Terms table for taxonomy terms
        Schema::create('taxonomy_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained('custom_taxonomies')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('taxonomy_terms')->onDelete('set null');
            $table->unsignedInteger('order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['taxonomy_id', 'slug']);
            $table->index(['taxonomy_id', 'parent_id']);
        });

        // Pivot table for entry-term relationships
        Schema::create('cpt_entry_term', function (Blueprint $table) {
            $table->foreignId('entry_id')->constrained('cpt_entries')->onDelete('cascade');
            $table->foreignId('term_id')->constrained('taxonomy_terms')->onDelete('cascade');
            $table->primary(['entry_id', 'term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpt_entry_term');
        Schema::dropIfExists('taxonomy_terms');
        Schema::dropIfExists('cpt_entries');
    }
};
