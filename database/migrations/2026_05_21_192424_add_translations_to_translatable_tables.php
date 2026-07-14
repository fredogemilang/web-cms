<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'pages' => 'settings',  // pages.settings exists
            'cpt_entries' => 'meta',      // cpt_entries.meta exists
        ];

        foreach ($tables as $tbl => $afterCol) {
            if (Schema::hasTable($tbl) && ! Schema::hasColumn($tbl, 'translations')) {
                Schema::table($tbl, function (Blueprint $table) use ($afterCol) {
                    $table->json('translations')->nullable()->after($afterCol);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['pages', 'cpt_entries'] as $tbl) {
            if (Schema::hasColumn($tbl, 'translations')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn('translations');
                });
            }
        }
    }
};
