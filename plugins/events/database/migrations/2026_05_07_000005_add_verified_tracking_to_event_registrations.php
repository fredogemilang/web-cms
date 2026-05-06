<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('user_id');

            $table->timestamp('verified_at')
                ->nullable()
                ->after('verified_by');

            $table->string('verified_type', 100)
                ->nullable()
                ->after('verified_at');

            $table->text('verified_note')
                ->nullable()
                ->after('verified_type');
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified_by', 'verified_at', 'verified_type', 'verified_note']);
        });
    }
};