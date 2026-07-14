<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Plugin tables (and their preferred column to anchor `translations` after).
     * Tables that don't exist (plugin not installed) are silently skipped.
     */
    protected array $targets = [
        'posts' => 'meta',
        'categories' => 'order',
        'tags' => 'slug',
        'events' => 'settings',
        'event_categories' => 'order',
        'speakers' => 'is_active',
        'membership_tiers' => 'icon',
        'membership_benefits' => 'order',
    ];

    public function up(): void
    {
        foreach ($this->targets as $tbl => $after) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }
            if (Schema::hasColumn($tbl, 'translations')) {
                continue;
            }

            Schema::table($tbl, function (Blueprint $table) use ($after, $tbl) {
                // Some "after" anchors may not exist if a table evolved differently —
                // fall back to no anchor in that case.
                if (Schema::hasColumn($tbl, $after)) {
                    $table->json('translations')->nullable()->after($after);
                } else {
                    $table->json('translations')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->targets) as $tbl) {
            if (! Schema::hasColumn($tbl, 'translations')) {
                continue;
            }
            Schema::table($tbl, function (Blueprint $table) {
                $table->dropColumn('translations');
            });
        }
    }
};
