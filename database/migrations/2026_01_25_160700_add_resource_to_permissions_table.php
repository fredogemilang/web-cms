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
            $table->string('resource', 100)->nullable()->after('module');
            
            $table->index('resource');
        });
        
        // Populate resource column from existing module values
        // For existing permissions, resource = module
        \DB::table('permissions')->whereNull('resource')->update([
            'resource' => \DB::raw('module')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['resource']);
            $table->dropColumn('resource');
        });
    }
};
