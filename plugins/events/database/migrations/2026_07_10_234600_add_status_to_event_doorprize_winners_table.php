<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('event_doorprize_winners', 'status')) {
            Schema::table('event_doorprize_winners', function (Blueprint $table) {
                $table->string('status')->default('active')->after('registration_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('event_doorprize_winners', 'status')) {
            Schema::table('event_doorprize_winners', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
