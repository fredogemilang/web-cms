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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version')->default('1.0.0');
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->string('screenshot')->nullable();

            // State
            $table->boolean('is_active')->default(false);

            // Metadata from theme.json
            $table->json('supports')->nullable();

            // Timestamps
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('slug');
        });

        // Seed default theme
        DB::table('themes')->insert([
            'name' => 'Default Theme',
            'slug' => 'default',
            'version' => '1.0.0',
            'description' => 'Clean, modern default theme for CMS',
            'author' => 'CMS Team',
            'is_active' => true,
            'supports' => json_encode(['menus', 'post-thumbnails']),
            'installed_at' => now(),
            'activated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
