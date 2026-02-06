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
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('source', 100)->default('core')->after('description');
            $table->string('plugin_slug', 100)->nullable()->after('source');
            $table->boolean('is_active')->default(true)->after('plugin_slug');
            $table->string('icon', 50)->nullable()->after('is_active');
            $table->integer('sort_order')->default(0)->after('icon');
            
            $table->index('source');
            $table->index('plugin_slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropIndex(['plugin_slug']);
            $table->dropIndex(['is_active']);
            
            $table->dropColumn(['source', 'plugin_slug', 'is_active', 'icon', 'sort_order']);
        });
    }
};
