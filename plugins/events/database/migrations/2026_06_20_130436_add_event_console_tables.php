<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add limit_by_quota to events table
        if (!Schema::hasColumn('events', 'limit_by_quota')) {
            Schema::table('events', function (Blueprint $table) {
                $table->boolean('limit_by_quota')->default(false)->after('max_participants');
            });
        }

        // Doorprize Sessions
        Schema::create('event_doorprize_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('require_checkin')->default(true);
            $table->boolean('require_feedback')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Doorprize Prizes
        Schema::create('event_doorprize_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('event_doorprize_sessions')->cascadeOnDelete();
            $table->string('name');
            $table->string('gift_description')->nullable();
            $table->integer('max_winners')->default(1);
            $table->string('image')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Doorprize Winners
        Schema::create('event_doorprize_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prize_id')->constrained('event_doorprize_prizes')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('event_registrations')->cascadeOnDelete();
            $table->timestamp('won_at')->useCurrent();
            $table->timestamps();
        });

        // Doorprize Bans (attendees banned from specific session)
        Schema::create('event_doorprize_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('event_doorprize_sessions')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('event_registrations')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'registration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_doorprize_bans');
        Schema::dropIfExists('event_doorprize_winners');
        Schema::dropIfExists('event_doorprize_prizes');
        Schema::dropIfExists('event_doorprize_sessions');

        if (Schema::hasColumn('events', 'limit_by_quota')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('limit_by_quota');
            });
        }
    }
};
