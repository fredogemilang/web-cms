<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PageTranslationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('default_locale', 'id', 'languages', 'string');
        Setting::set('available_locales', 'id,en', 'languages', 'string');
    }

    #[Test]
    public function translate_returns_per_locale_value_with_default_fallback(): void
    {
        $page = $this->makePage('home');
        $page->setTranslation('title', 'en', 'Welcome Home');
        $page->save();

        app()->setLocale('id');
        $this->assertSame('Home', $page->translate('title'));

        app()->setLocale('en');
        $this->assertSame('Welcome Home', $page->translate('title'));

        // Missing translation falls back to default-locale value
        app()->setLocale('fr'); // not configured — should fall back
        $this->assertSame('Home', $page->translate('title'));
    }

    #[Test]
    public function find_by_localized_slug_finds_default_slug_without_switching_locale(): void
    {
        $page = $this->makePage('home');
        app()->setLocale('id');

        $found = Page::findByLocalizedSlug('home');

        $this->assertNotNull($found);
        $this->assertSame($page->id, $found->id);
        $this->assertSame('id', app()->getLocale());
    }

    #[Test]
    public function find_by_localized_slug_auto_switches_locale_on_translated_slug_match(): void
    {
        $page = $this->makePage('home');
        $page->setTranslation('title', 'en', 'Welcome');
        $page->setTranslation('slug', 'en', 'welcome');
        $page->save();

        app()->setLocale('id');

        $found = Page::findByLocalizedSlug('welcome');

        $this->assertNotNull($found);
        $this->assertSame($page->id, $found->id);
        $this->assertSame('en', app()->getLocale());
    }

    #[Test]
    public function find_by_localized_slug_returns_null_for_unknown_slug(): void
    {
        $this->makePage('home');

        $this->assertNull(Page::findByLocalizedSlug('does-not-exist'));
    }

    #[Test]
    public function set_translation_stores_in_json_column_not_in_default_field(): void
    {
        $page = $this->makePage('home');
        $page->setTranslation('title', 'en', 'English Title');
        $page->save();

        $this->assertSame('Home', $page->fresh()->title);              // default column untouched
        $this->assertSame('English Title', $page->fresh()->translations['en']['title']);
    }

    protected function makePage(string $slug): Page
    {
        $author = User::factory()->create();

        return Page::create([
            'title' => ucfirst($slug),
            'slug' => $slug,
            'status' => 'published',
            'author_id' => $author->id,
            'template' => 'default',
        ]);
    }
}
