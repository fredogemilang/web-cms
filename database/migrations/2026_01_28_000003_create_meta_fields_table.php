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
        Schema::create('meta_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Field name/key
            $table->string('label');
            $table->string('type');              // text, textarea, wysiwyg, number, select, etc
            $table->text('description')->nullable();
            $table->json('options')->nullable(); // For select/radio: choices, for number: min/max
            $table->json('validation')->nullable();
            $table->string('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->morphs('fieldable');         // Polymorphic: can belong to CPT or Taxonomy
            $table->string('field_group')->nullable();
            $table->json('conditional_logic')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['fieldable_type', 'fieldable_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_fields');
    }
};
