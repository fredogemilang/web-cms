<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');
            $table->foreignId('parent_block_id')->nullable()
                ->constrained('page_blocks')->onDelete('cascade');

            // Block Identification
            $table->string('name');
            $table->string('type');
            $table->string('label')->nullable();

            // Block Content
            $table->text('value')->nullable();
            $table->json('options')->nullable();

            // Ordering
            $table->unsignedInteger('order')->default(0);

            // Visibility
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['page_id', 'order']);
            $table->index(['page_id', 'parent_block_id', 'order']);
            $table->unique(['page_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
