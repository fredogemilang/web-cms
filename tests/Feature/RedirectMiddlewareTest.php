<?php

namespace Tests\Feature;

use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedirectMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function exact_path_match_returns_configured_status_code(): void
    {
        Redirect::create([
            'from_path' => '/old-about',
            'to_url' => '/about',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/old-about')
            ->assertStatus(301)
            ->assertRedirect('/about');
    }

    #[Test]
    public function regex_path_with_capture_group_substitutes_into_target(): void
    {
        Redirect::create([
            'from_path' => '^/blog/(\d+)$',
            'to_url' => '/posts/$1',
            'status_code' => 302,
            'is_regex' => true,
            'is_active' => true,
        ]);

        $this->get('/blog/42')
            ->assertStatus(302)
            ->assertRedirect('/posts/42');
    }

    #[Test]
    public function inactive_rules_are_not_applied(): void
    {
        Redirect::create([
            'from_path' => '/disabled-path',
            'to_url' => '/somewhere',
            'status_code' => 301,
            'is_active' => false,
        ]);

        // Without a matching rule and without a route, expect 404 (not redirect).
        $this->get('/disabled-path')->assertStatus(404);
    }

    #[Test]
    public function admin_path_is_never_intercepted_even_when_a_rule_matches(): void
    {
        Redirect::create([
            'from_path' => '/ctrlpanel/login',
            'to_url' => '/somewhere',
            'status_code' => 301,
            'is_active' => true,
        ]);

        // /ctrlpanel/login is the admin login route — should reach Laravel's
        // routing, NOT the redirect rule.
        $response = $this->get('/ctrlpanel/login');
        $response->assertHeaderMissing('Location', '/somewhere');
    }

    #[Test]
    public function hit_counter_increments_after_redirect(): void
    {
        $rule = Redirect::create([
            'from_path' => '/counted',
            'to_url' => '/elsewhere',
            'status_code' => 302,
            'is_active' => true,
        ]);

        $this->get('/counted')->assertRedirect('/elsewhere');
        $this->get('/counted')->assertRedirect('/elsewhere');

        $this->assertSame(2, $rule->fresh()->hit_count);
        $this->assertNotNull($rule->fresh()->last_hit_at);
    }

    #[Test]
    public function query_string_is_preserved_when_target_has_none(): void
    {
        Redirect::create([
            'from_path' => '/old',
            'to_url' => '/new',
            'status_code' => 301,
            'is_active' => true,
        ]);

        // Symfony normalises (alphabetises) the query string — compare loosely.
        $response = $this->get('/old?utm_source=email&id=42');
        $location = $response->headers->get('Location');

        $this->assertStringContainsString('/new?', $location);
        $this->assertStringContainsString('utm_source=email', $location);
        $this->assertStringContainsString('id=42', $location);
    }
}
