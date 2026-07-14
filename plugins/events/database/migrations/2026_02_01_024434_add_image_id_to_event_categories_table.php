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
        Schema::table('event_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('event_categories', 'image_id')) {
                $table->unsignedBigInteger('image_id')->nullable()->after('icon');
                $table->foreign('image_id')->references('id')->on('media')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_categories', function (Blueprint $table) {
            if (Schema::hasColumn('event_categories', 'image_id')) {
                try {
                    $table->dropForeign(['image_id']);
                } catch (Throwable $e) { /* FK may not exist */
                }
                $table->dropColumn('image_id');
            }
        });
    }
};
