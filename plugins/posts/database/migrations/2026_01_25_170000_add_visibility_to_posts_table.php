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
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'visibility')) {
                $table->enum('visibility', ['public', 'private', 'password'])->default('public')->after('status');
            }
            if (!Schema::hasColumn('posts', 'password')) {
                $table->string('password')->nullable()->after('visibility');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $drops = array_values(array_filter(
                ['visibility', 'password'],
                fn ($col) => Schema::hasColumn('posts', $col)
            ));
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
