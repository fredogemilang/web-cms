<?php

namespace Plugins\Events\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds fields for wizard, banner, email config, success page, and registration enhancements.
     * See PRD 01 section "Database Schema Changes"
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Banner
            $table->string('banner_image', 255)->nullable()->after('featured_image_id');

            // Registration date range (replaces single registration_deadline logic)
            $table->timestamp('registration_start_date')->nullable()->after('registration_deadline');
            $table->timestamp('registration_end_date')->nullable()->after('registration_start_date');

            // Corporate email requirement
            $table->boolean('requires_corporate_email')->default(false)->after('registration_requires_approval');

            // Per-event email sender config
            $table->boolean('sending_email')->default(true)->after('max_participants');
            $table->string('sender_email', 255)->nullable()->after('sending_email');
            $table->string('sender_name', 255)->nullable()->after('sender_email');
            $table->string('cc_to_email', 255)->nullable()->after('sender_name');

            // Success page configuration
            $table->string('success_title', 255)->nullable()->after('status');
            $table->text('success_desc')->nullable()->after('success_title');
            $table->string('success_button', 100)->nullable()->after('success_desc');
            $table->enum('success_link_type', ['event', 'custom'])->default('event')->after('success_button');
            $table->string('success_link', 500)->nullable()->after('success_link_type');

            // Display flags
            $table->boolean('show_registered_count')->default(false)->after('success_link');
            $table->boolean('enable_track_session')->default(false)->after('show_registered_count');

            // Wizard step tracking (for draft save across steps)
            $table->unsignedTinyInteger('wizard_step')->default(0)->after('enable_track_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'banner_image',
                'registration_start_date',
                'registration_end_date',
                'requires_corporate_email',
                'sending_email',
                'sender_email',
                'sender_name',
                'cc_to_email',
                'success_title',
                'success_desc',
                'success_button',
                'success_link_type',
                'success_link',
                'show_registered_count',
                'enable_track_session',
                'wizard_step',
            ]);
        });
    }
};
