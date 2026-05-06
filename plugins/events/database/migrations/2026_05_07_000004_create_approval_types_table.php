<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();
            $table->enum('cat', ['approved', 'rejected']);
            $table->string('type_name', 100);
            $table->string('email_subject', 255);
            $table->string('email_banner', 500)->nullable();
            $table->text('email_body');
            $table->timestamps();

            $table->index(['event_id', 'cat'], 'idx_approval_types_event_cat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_types');
    }
};