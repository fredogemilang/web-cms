<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function creating_a_page_writes_a_page_created_activity(): void
    {
        $this->actingAs(User::factory()->create());

        Page::create([
            'title' => 'Sample',
            'slug' => 'sample',
            'status' => 'draft',
            'template' => 'default',
            'author_id' => auth()->id(),
        ]);

        $this->assertDatabaseHas('activities', ['action' => 'page.created']);
    }

    #[Test]
    public function updating_a_page_records_old_and_new_values_in_properties(): void
    {
        $this->actingAs(User::factory()->create());

        $page = Page::create([
            'title' => 'Original',
            'slug' => 'sample-'.uniqid(),
            'status' => 'draft',
            'template' => 'default',
            'author_id' => auth()->id(),
        ]);

        $page->update(['title' => 'Renamed', 'status' => 'published']);

        $activity = Activity::where('action', 'page.updated')->latest('id')->first();
        $this->assertNotNull($activity);
        $this->assertSame('Original', $activity->properties['old']['title']);
        $this->assertSame('Renamed', $activity->properties['new']['title']);
        $this->assertSame('draft', $activity->properties['old']['status']);
        $this->assertSame('published', $activity->properties['new']['status']);
    }

    #[Test]
    public function password_field_is_masked_in_activity_properties(): void
    {
        $this->actingAs(User::factory()->create());

        $user = User::create([
            'name' => 'Sensitive User',
            'email' => 'sens-'.uniqid().'@example.com',
            'password' => bcrypt('initial-password'),
        ]);

        $user->update(['password' => bcrypt('new-password')]);

        $activity = Activity::where('action', 'user.updated')->latest('id')->first();
        $this->assertNotNull($activity);
        $this->assertSame('••• redacted •••', $activity->properties['new']['password'] ?? null);
    }

    #[Test]
    public function deleting_a_page_writes_page_deleted_activity(): void
    {
        $this->actingAs(User::factory()->create());

        $page = Page::create([
            'title' => 'To delete',
            'slug' => 'del-'.uniqid(),
            'status' => 'draft',
            'template' => 'default',
            'author_id' => auth()->id(),
        ]);

        $page->delete();

        $this->assertDatabaseHas('activities', ['action' => 'page.deleted']);
    }
}
