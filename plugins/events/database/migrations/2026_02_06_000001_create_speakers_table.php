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
        if (!Schema::hasTable('speakers')) {
            Schema::create('speakers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('title')->nullable()->comment('Job Title');
                $table->string('company')->nullable();
                $table->text('bio')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                
                $table->unsignedBigInteger('photo_id')->nullable();
                
                $table->string('linkedin_url')->nullable();
                $table->string('twitter_url')->nullable();
                $table->string('facebook_url')->nullable();
                $table->string('instagram_url')->nullable();
                $table->string('website')->nullable();
                
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speakers');
    }
};
