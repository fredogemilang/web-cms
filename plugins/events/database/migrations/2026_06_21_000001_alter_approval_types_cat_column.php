<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE approval_types MODIFY COLUMN cat VARCHAR(50) NOT NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First delete any rows with categories other than approved/rejected to prevent database errors
        DB::table('approval_types')->whereNotIn('cat', ['approved', 'rejected'])->delete();
        
        DB::statement("ALTER TABLE approval_types MODIFY COLUMN cat ENUM('approved', 'rejected') NOT NULL;");
    }
};
