<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::resetEncryptedKeyCache();
    }

    #[Test]
    public function setting_roundtrip_preserves_typed_value(): void
    {
        Setting::set('count', 5, 'test', 'integer');
        Setting::set('enabled', true, 'test', 'boolean');
        Setting::set('site_name', 'My Site', 'test', 'string');
        Setting::set('tags', ['a', 'b'], 'test', 'array');

        $this->assertSame(5, Setting::get('count'));
        $this->assertSame(true, Setting::get('enabled'));
        $this->assertSame('My Site', Setting::get('site_name'));
        $this->assertSame(['a', 'b'], Setting::get('tags'));
    }

    #[Test]
    public function get_returns_default_when_key_missing(): void
    {
        $this->assertSame('fallback', Setting::get('missing_key', 'fallback'));
    }

    #[Test]
    public function sensitive_password_field_is_encrypted_at_rest(): void
    {
        // Register a group with a password-type field so the registry marks it encrypted
        app(SettingsRegistry::class)->registerGroup('secret_test', [
            'fields' => [
                ['key' => 'secret_api_key', 'label' => 'Key', 'type' => 'password'],
            ],
        ]);
        Setting::resetEncryptedKeyCache();

        Setting::set('secret_api_key', 'xkeysib-PLAINTEXT', 'secret_test', 'string');

        // Raw DB column should NOT contain the plaintext
        $row = Setting::where('key', 'secret_api_key')->first();
        $rawValue = $row->value['v'] ?? null;
        $this->assertNotSame('xkeysib-PLAINTEXT', $rawValue);
        $this->assertStringNotContainsString('xkeysib-PLAINTEXT', json_encode($row->value));

        // But retrieval via Setting::get must transparently decrypt
        $this->assertSame('xkeysib-PLAINTEXT', Setting::get('secret_api_key'));
    }

    #[Test]
    public function non_sensitive_field_remains_plaintext_in_storage(): void
    {
        Setting::set('site_name', 'Acme', 'general', 'string');

        $row = Setting::where('key', 'site_name')->first();
        $this->assertSame('Acme', $row->value['v']);
    }

    #[Test]
    public function changing_a_setting_writes_an_audit_entry(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Setting::set('site_name', 'Original', 'general', 'string');
        Setting::set('site_name', 'Updated', 'general', 'string');

        $audits = Activity::where('action', 'setting.updated')->get();
        $this->assertGreaterThanOrEqual(1, $audits->count());
        $last = $audits->last();
        $this->assertSame('site_name', $last->properties['key']);
    }
}
