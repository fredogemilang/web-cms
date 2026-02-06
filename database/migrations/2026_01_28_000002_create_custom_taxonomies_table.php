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
        Schema::create('custom_taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Internal name
            $table->string('singular_label');
            $table->string('plural_label');
            $table->string('slug')->unique();
            $table->boolean('is_hierarchical')->default(true);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_rest')->default(true);
            $table->json('post_types');          // Array of CPT slugs this taxonomy belongs to
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_taxonomies');
    }
};
