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
        // Enhance forms table
        Schema::table('forms', function (Blueprint $table) {
            $table->string('form_type')->default('standard')->after('is_active'); // standard, multi_step, conversational
            $table->json('steps')->nullable()->after('form_type'); // for multi-step: [{title, description, order}]
            $table->json('notifications')->nullable()->after('steps'); // email notification configs
            $table->json('confirmations')->nullable()->after('notifications'); // success messages, redirects
            $table->json('spam_protection')->nullable()->after('confirmations'); // honeypot, recaptcha settings
            $table->boolean('has_conditional_logic')->default(false)->after('spam_protection');
            $table->integer('total_entries')->default(0)->after('has_conditional_logic'); // denormalized count
            $table->string('submit_button_text')->default('Submit')->after('total_entries');
            $table->json('styling')->nullable()->after('submit_button_text'); // form styling options
        });

        // Enhance form_fields table
        Schema::table('form_fields', function (Blueprint $table) {
            $table->json('conditional_logic')->nullable()->after('default_value'); // show/hide rules
            $table->string('column_width')->default('full')->after('conditional_logic'); // full, half, third, quarter
            $table->integer('step_index')->nullable()->after('column_width'); // for multi-step forms
            $table->json('advanced_settings')->nullable()->after('step_index'); // min/max values, file types, input masks, etc.
            $table->string('css_class')->nullable()->after('advanced_settings'); // custom CSS class
            $table->boolean('is_hidden')->default(false)->after('css_class'); // hidden field type
        });

        // Create form_conditional_rules table for complex conditional logic
        Schema::create('form_conditional_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_field_id')->constrained()->onDelete('cascade');
            $table->string('action'); // show, hide
            $table->string('match_type')->default('all'); // all, any
            $table->json('conditions'); // [{field_id, operator, value}]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create form_steps table for multi-step forms
        Schema::create('form_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->json('settings')->nullable(); // step-specific settings
            $table->timestamps();
        });

        // Add status to form_entries for draft/complete tracking
        Schema::table('form_entries', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('user_id'); // draft, completed
            $table->integer('current_step')->nullable()->after('status'); // for multi-step save & resume
            $table->string('session_token')->nullable()->after('current_step'); // for anonymous draft saving
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove form_entries additions
        Schema::table('form_entries', function (Blueprint $table) {
            $table->dropColumn(['status', 'current_step', 'session_token']);
        });

        // Drop new tables
        Schema::dropIfExists('form_steps');
        Schema::dropIfExists('form_conditional_rules');

        // Remove form_fields additions
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropColumn(['conditional_logic', 'column_width', 'step_index', 'advanced_settings', 'css_class', 'is_hidden']);
        });

        // Remove forms additions
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['form_type', 'steps', 'notifications', 'confirmations', 'spam_protection', 'has_conditional_logic', 'total_entries', 'submit_button_text', 'styling']);
        });
    }
};
