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
        Schema::create('custom_post_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Internal name (lowercase, underscores)
            $table->string('singular_label');    // "Product"
            $table->string('plural_label');      // "Products"
            $table->string('slug')->unique();    // URL slug
            $table->string('icon')->default('article');
            $table->text('description')->nullable();
            $table->boolean('is_hierarchical')->default(false);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_rest')->default(true);
            $table->boolean('has_archive')->default(true);
            $table->json('supports')->nullable(); // ['title', 'editor', 'thumbnail', 'excerpt', etc]
            $table->json('settings')->nullable(); // Additional settings
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_post_types');
    }
};
