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
        if (!Schema::hasTable('event_speaker')) {
            Schema::create('event_speaker', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('speaker_id');
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->unique(['event_id', 'speaker_id']);
                
                // Foreign keys
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->foreign('speaker_id')->references('id')->on('speakers')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_speaker');
    }
};
