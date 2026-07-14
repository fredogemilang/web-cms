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
        if (Schema::hasTable('page_blocks') && ! Schema::hasColumn('page_blocks', 'translations')) {
            Schema::table('page_blocks', function (Blueprint $table) {
                $table->json('translations')->nullable()->after('options');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('page_blocks', 'translations')) {
            Schema::table('page_blocks', function (Blueprint $table) {
                $table->dropColumn('translations');
            });
        }
    }
};
