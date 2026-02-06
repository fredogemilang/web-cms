<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('job_level')->nullable();
            $table->string('job_title')->nullable();
            $table->string('domicile')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('institution')->nullable();
            $table->string('education_level')->nullable();
            $table->string('industry')->nullable();
            $table->string('article_file')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, approved, rejected
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_submissions');
    }
};
