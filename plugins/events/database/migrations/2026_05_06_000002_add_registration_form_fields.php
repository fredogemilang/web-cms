<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PRD 02: Registration Form Enhancements
     *
     * Adds:
     * - New columns on event_registrations (UUID, QR, salutation, company, referral, consent, walk-in)
     * - free_email_domains lookup table (corporate email validation)
     * - contact_levels and contact_divisions dropdown tables
     * - tracking_codes for referral tracking
     */
    public function up(): void
    {
        // ── event_registrations new columns ──────────────────────────────────
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('full_name', 255)->nullable()->after('user_id');
            $table->string('salutation', 10)->nullable()->after('full_name');
            $table->string('company_name', 255)->nullable()->after('salutation');
            $table->string('company_type', 50)->nullable()->after('company_name');
            $table->string('job_title', 255)->nullable()->after('company_type');
            $table->unsignedBigInteger('contact_level_id')->nullable()->after('job_title');
            $table->unsignedBigInteger('contact_divisi_id')->nullable()->after('contact_level_id');
            $table->string('contact_divisi_name', 255)->nullable()->after('contact_divisi_id');
            $table->string('country_code', 10)->default('+62')->after('contact_divisi_name');
            $table->string('mobile_phone', 20)->nullable()->after('country_code');
            $table->uuid('uuid')->nullable()->after('mobile_phone');
            $table->string('qr_image', 255)->nullable()->after('uuid');
            $table->string('referral_code', 50)->nullable()->after('qr_image');
            $table->string('referral_source', 255)->nullable()->after('referral_code');
            $table->timestamp('consent_accepted_at')->nullable()->after('referral_source');
            $table->boolean('walk_in')->default(false)->after('consent_accepted_at');
            $table->boolean('check_in')->default(false)->after('walk_in');
            $table->timestamp('check_in_date')->nullable()->after('check_in');
            $table->string('registration_type', 20)->default('normal')->after('check_in_date');
        });

        // Add indexes
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->index('uuid', 'idx_uuid');
            $table->index(['event_id', 'email'], 'idx_event_email');
        });

        // ── free_email_domains (corporate email validation) ───────────────────
        Schema::create('free_email_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed known free email domains
        DB::table('free_email_domains')->insert([
            ['domain' => 'gmail.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'yahoo.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'hotmail.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'outlook.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'aol.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'icloud.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'live.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'msn.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'ymail.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'rocketmail.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'mail.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'gmx.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'protonmail.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'tutanota.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'zoho.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'inbox.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'rediffmail.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'mailinator.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'tempmail.org', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => '10minutemail.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'guerrillamail.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── contact_levels ────────────────────────────────────────────────────
        Schema::create('contact_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('level');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('contact_levels')->insert([
            ['name' => 'Director / C-Level', 'level' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vice President', 'level' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'General Manager / Senior Manager', 'level' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manager', 'level' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supervisor / Team Lead', 'level' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Staff / Individual Contributor', 'level' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Associate / Junior', 'level' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Intern / Trainee', 'level' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Freelance / Consultant', 'level' => 9, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'level' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── contact_divisions ──────────────────────────────────────────────────
        Schema::create('contact_divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('contact_divisions')->insert([
            ['name' => 'Engineering / IT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Marketing', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Finance / Accounting', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Human Resources', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── tracking_codes ────────────────────────────────────────────────────
        Schema::create('tracking_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('tracking_code', 50)->unique();
            $table->string('source', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['event_id', 'tracking_code'], 'idx_event_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_codes');
        Schema::dropIfExists('contact_divisions');
        Schema::dropIfExists('contact_levels');
        Schema::dropIfExists('free_email_domains');

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropIndex('idx_uuid');
            $table->dropIndex('idx_event_email');
            $table->dropColumn([
                'full_name', 'salutation', 'company_name', 'company_type', 'job_title',
                'contact_level_id', 'contact_divisi_id', 'contact_divisi_name',
                'country_code', 'mobile_phone', 'uuid', 'qr_image',
                'referral_code', 'referral_source', 'consent_accepted_at',
                'walk_in', 'check_in', 'check_in_date', 'registration_type',
            ]);
        });
    }
};
