<?php

namespace Tests\Feature;

use App\Jobs\SendFormNotificationJob;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\FormField;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    }

    #[Test]
    public function ajax_submission_returns_200_and_creates_entry(): void
    {
        Queue::fake();
        $form = $this->makeContactForm();

        $response = $this->postJson("/forms/{$form->slug}/ajax", [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'message' => 'Hello',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(1, FormEntry::where('form_id', $form->id)->count());
    }

    #[Test]
    public function submission_validation_returns_422_with_field_errors(): void
    {
        $form = $this->makeContactForm();

        $response = $this->postJson("/forms/{$form->slug}/ajax", [
            // missing required `name` and `email`
            'message' => 'Hello',
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, FormEntry::count());
    }

    #[Test]
    public function notification_job_is_queued_when_form_has_notifications_enabled(): void
    {
        Queue::fake();
        $form = $this->makeContactForm([
            'notifications' => ['enabled' => true, 'admin_email' => 'admin@example.com'],
        ]);

        $this->postJson("/forms/{$form->slug}/ajax", [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'message' => 'Hi',
        ])->assertOk();

        Queue::assertPushed(SendFormNotificationJob::class, function ($job) use ($form) {
            return $job->formId === $form->id;
        });
    }

    #[Test]
    public function honeypot_silently_rejects_bot_submissions(): void
    {
        Queue::fake();
        $form = $this->makeContactForm([
            'spam_protection' => ['honeypot' => true],
        ]);

        $this->postJson("/forms/{$form->slug}/ajax", [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'spam',
            'website_url' => 'http://botsite.com', // honeypot trap
        ])->assertStatus(422);

        $this->assertSame(0, FormEntry::count());
        Queue::assertNothingPushed();
    }

    protected function makeContactForm(array $overrides = []): Form
    {
        $form = Form::create(array_merge([
            'name' => 'Contact',
            'slug' => 'contact',
            'description' => null,
            'is_active' => true,
            'form_type' => 'standard',
            'confirmations' => ['type' => 'message', 'message' => 'Thanks!'],
        ], $overrides));

        $fields = [
            ['field_id' => 'name',    'label' => 'Name',    'type' => 'text',     'is_required' => true, 'order' => 0],
            ['field_id' => 'email',   'label' => 'Email',   'type' => 'email',    'is_required' => true, 'order' => 1],
            ['field_id' => 'message', 'label' => 'Message', 'type' => 'textarea', 'is_required' => false, 'order' => 2],
        ];

        foreach ($fields as $f) {
            FormField::create($f + ['form_id' => $form->id]);
        }

        return $form->fresh('fields');
    }
}
