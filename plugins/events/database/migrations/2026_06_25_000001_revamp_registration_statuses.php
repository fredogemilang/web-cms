<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Renames registration statuses:
     *   confirmed → approved
     *   cancelled → rejected
     *   attended  → approved (with check_in = 1)
     *
     * Renames columns:
     *   confirmed_at → approved_at
     *   cancelled_at → rejected_at
     */
    public function up(): void
    {
        // 1. Data migration — update existing rows BEFORE changing enum
        //    Ensure any 'attended' registrations have check_in set
        DB::table('event_registrations')
            ->where('status', 'attended')
            ->where('check_in', false)
            ->update(['check_in' => true]);

        //    Temporarily allow all values by switching to VARCHAR
        DB::statement("ALTER TABLE event_registrations MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");

        //    Rename status values
        DB::table('event_registrations')
            ->whereIn('status', ['confirmed', 'attended'])
            ->update(['status' => 'approved']);

        DB::table('event_registrations')
            ->where('status', 'cancelled')
            ->update(['status' => 'rejected']);

        // 2. Schema changes — set new enum and rename columns
        DB::statement("ALTER TABLE event_registrations MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->renameColumn('confirmed_at', 'approved_at');
            $table->renameColumn('cancelled_at', 'rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename columns back
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->renameColumn('approved_at', 'confirmed_at');
            $table->renameColumn('rejected_at', 'cancelled_at');
        });

        // Switch to VARCHAR temporarily
        DB::statement("ALTER TABLE event_registrations MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");

        // Revert status values
        DB::table('event_registrations')
            ->where('status', 'approved')
            ->update(['status' => 'confirmed']);

        DB::table('event_registrations')
            ->where('status', 'rejected')
            ->update(['status' => 'cancelled']);

        // Restore original enum
        DB::statement("ALTER TABLE event_registrations MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'attended') NOT NULL DEFAULT 'pending'");
    }
};
