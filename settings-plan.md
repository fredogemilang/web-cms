# Settings Implementation Plan untuk Web CMS

## Ringkasan Aplikasi

Aplikasi ini adalah **Laravel 12 CMS** dengan arsitektur:
- Livewire 4.0 untuk komponen interaktif
- Plugin system untuk extensibility
- TailwindCSS v4 untuk styling
- SQLite database

### File-file Penting untuk Settings:
- `plugins/posts/src/Livewire/Settings.php` - Contoh implementasi settings
- `plugins/posts/src/Models/Setting.php` - Model untuk settings storage
- `app/Events/RenderAdminMenu.php` - Event untuk menambah menu admin
- `config/app.php`, `config/mail.php`, `config/cms.php` - Config files

---

## 1. Settings > General

**Rekomendasi pengaturan yang bisa ditaruh:**

### Site Identity
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `site_name` | string | 'Web CMS' | Nama website |
| `site_tagline` | string | '' | Tagline/slogan website |
| `site_logo` | media | null | Logo utama |
| `site_favicon` | media | null | Favicon |
| `admin_email` | email | '' | Email administrator |

### Regional Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `timezone` | select | 'Asia/Jakarta' | Zona waktu |
| `date_format` | select | 'd M Y' | Format tanggal |
| `time_format` | select | 'H:i' | Format waktu |

### Content Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `homepage_type` | select | 'page' | Tipe homepage (page/posts) |
| `homepage_page_id` | select | null | Pilih halaman untuk homepage |
| `posts_page_id` | select | null | Halaman untuk daftar posts |
| `items_per_page` | number | 10 | Item per halaman |

### Maintenance Mode
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `maintenance_mode` | boolean | false | Aktifkan mode maintenance |
| `maintenance_message` | textarea | '' | Pesan saat maintenance |
| `maintenance_allowed_ips` | textarea | '' | IP yang diizinkan akses |

---

### Implementasi General Settings dari Hulu ke Hilir

#### 1. Core Settings Model - Setting.php

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    protected static array $cache = [];

    // ========== STATIC METHODS ==========

    public static function get(string $key, $default = null)
    {
        // Check memory cache first
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        // Check database cache
        $value = Cache::remember("setting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? static::castValue($setting->value, $setting->type) : $default;
        });

        static::$cache[$key] = $value;

        return $value;
    }

    public static function set(string $key, $value, ?string $group = null, ?string $type = null): void
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'group' => $group ?? static::guessGroup($key),
                'type' => $type ?? static::guessType($value),
            ]
        );

        // Clear caches
        Cache::forget("setting:{$key}");
        unset(static::$cache[$key]);
    }

    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn($s) => [$s->key => static::castValue($s->value, $s->type)])
            ->toArray();
    }

    public static function setMany(array $settings, ?string $group = null): void
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value, $group);
        }
    }

    // ========== HELPERS ==========

    protected static function castValue($value, ?string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'number' => (int) $value,
            'float' => (float) $value,
            'array', 'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    protected static function guessType($value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    protected static function guessGroup(string $key): string
    {
        $prefixes = [
            'site_' => 'general',
            'brevo_' => 'brevo',
            'meta_' => 'seo',
            'og_' => 'seo',
            'sitemap_' => 'seo',
            'redirect_' => 'redirect',
            'lscache_' => 'lscache',
            'cdn_' => 'cdn',
            'img_' => 'image_optm',
            'css_' => 'page_optm',
            'js_' => 'page_optm',
        ];

        foreach ($prefixes as $prefix => $group) {
            if (str_starts_with($key, $prefix)) {
                return $group;
            }
        }

        return 'general';
    }

    // ========== CLEAR ALL CACHE ==========

    public static function clearCache(): void
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting:{$key}");
        }
        static::$cache = [];
    }
}
```

#### 2. Helper Function - setting()

```php
// app/helpers.php

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return \App\Models\Setting::get($key, $default);
    }
}

if (!function_exists('set_setting')) {
    function set_setting(string $key, $value, ?string $group = null): void
    {
        \App\Models\Setting::set($key, $value, $group);
    }
}
```

```json
// composer.json - autoload
"autoload": {
    "files": [
        "app/helpers.php"
    ]
}
```

#### 3. Integrasi dengan Layout - Site Identity

```html
<!-- resources/views/layouts/app.blade.php -->

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicon -->
    @if($favicon = setting('site_favicon'))
    <link rel="icon" href="{{ $favicon }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $favicon }}" type="image/x-icon">
    @endif

    <!-- Site Name untuk SEO -->
    <meta property="og:site_name" content="{{ setting('site_name', config('app.name')) }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- Header dengan Logo -->
    <header>
        <a href="{{ url('/') }}">
            @if($logo = setting('site_logo'))
                <img src="{{ $logo }}" alt="{{ setting('site_name') }}" class="h-10">
            @else
                <span class="text-xl font-bold">{{ setting('site_name', 'Web CMS') }}</span>
            @endif
        </a>
        @if($tagline = setting('site_tagline'))
            <p class="text-sm text-gray-500">{{ $tagline }}</p>
        @endif
    </header>

    @yield('content')

    <footer>
        <p>&copy; {{ date('Y') }} {{ setting('site_name', 'Web CMS') }}</p>
    </footer>
</body>
</html>
```

#### 4. Integrasi dengan Timezone & Date Format

```php
// app/Providers/AppServiceProvider.php

public function boot()
{
    // Set timezone from settings
    $timezone = setting('timezone', config('app.timezone'));
    config(['app.timezone' => $timezone]);
    date_default_timezone_set($timezone);

    // Register Blade directives for date formatting
    Blade::directive('date', function ($expression) {
        return "<?php echo \Carbon\Carbon::parse({$expression})->format(setting('date_format', 'd M Y')); ?>";
    });

    Blade::directive('time', function ($expression) {
        return "<?php echo \Carbon\Carbon::parse({$expression})->format(setting('time_format', 'H:i')); ?>";
    });

    Blade::directive('datetime', function ($expression) {
        return "<?php echo \Carbon\Carbon::parse({$expression})->format(setting('date_format', 'd M Y') . ' ' . setting('time_format', 'H:i')); ?>";
    });
}
```

Penggunaan di Blade:
```html
<p>Published: @date($post->published_at)</p>
<p>At: @time($post->published_at)</p>
<p>Full: @datetime($post->published_at)</p>
```

#### 5. Homepage Configuration

```php
// app/Http/Controllers/PageController.php

public function home()
{
    $homepageType = setting('homepage_type', 'page');

    if ($homepageType === 'posts') {
        // Show latest posts
        $posts = Post::where('status', 'published')
            ->orderByDesc('published_at')
            ->paginate(setting('items_per_page', 10));

        return view('posts.index', compact('posts'));
    }

    // Show static page
    $pageId = setting('homepage_page_id');

    if ($pageId) {
        $page = Page::findOrFail($pageId);
        return view('pages.show', compact('page'));
    }

    // Fallback: show default welcome or first page
    $page = Page::where('status', 'published')->first();

    if ($page) {
        return view('pages.show', compact('page'));
    }

    return view('welcome');
}
```

```php
// routes/web.php

Route::get('/', [PageController::class, 'home'])->name('home');
```

#### 6. Maintenance Mode Middleware

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        if (!setting('maintenance_mode', false)) {
            return $next($request);
        }

        // Check if user IP is allowed
        $allowedIps = setting('maintenance_allowed_ips', '');
        $allowedIpList = array_filter(array_map('trim', explode("\n", $allowedIps)));

        if (in_array($request->ip(), $allowedIpList)) {
            return $next($request);
        }

        // Check if user is admin
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            return $next($request);
        }

        // Check if accessing admin area
        if ($request->is(config('admin.path', 'admin') . '*')) {
            return $next($request);
        }

        // Show maintenance page
        $message = setting('maintenance_message', 'We are currently performing maintenance. Please check back soon.');

        return response()->view('maintenance', [
            'message' => $message,
        ], 503);
    }
}
```

```php
// Register middleware
// bootstrap/app.php

->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\CheckMaintenanceMode::class,
    ]);
})
```

#### 7. Maintenance View

```html
<!-- resources/views/maintenance.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance - {{ setting('site_name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f3f4f6;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 500px;
        }
        h1 { color: #1f2937; }
        p { color: #6b7280; line-height: 1.6; }
        .icon { font-size: 4rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔧</div>
        <h1>Under Maintenance</h1>
        <p>{!! nl2br(e($message)) !!}</p>
        @if($logo = setting('site_logo'))
            <img src="{{ $logo }}" alt="{{ setting('site_name') }}" style="max-width: 150px; margin-top: 2rem;">
        @endif
    </div>
</body>
</html>
```

#### 8. Admin Settings Form - GeneralSettings.php

```php
namespace App\Livewire\Admin\Settings;

use App\Models\Page;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithFileUploads;

class GeneralSettings extends Component
{
    use WithFileUploads;

    // Site Identity
    public string $site_name = '';
    public string $site_tagline = '';
    public $site_logo;
    public $site_favicon;
    public ?string $site_logo_url = null;
    public ?string $site_favicon_url = null;
    public string $admin_email = '';

    // Regional
    public string $timezone = 'Asia/Jakarta';
    public string $date_format = 'd M Y';
    public string $time_format = 'H:i';

    // Content
    public string $homepage_type = 'page';
    public ?int $homepage_page_id = null;
    public ?int $posts_page_id = null;
    public int $items_per_page = 10;

    // Maintenance
    public bool $maintenance_mode = false;
    public string $maintenance_message = '';
    public string $maintenance_allowed_ips = '';

    public function mount()
    {
        $this->site_name = setting('site_name', '');
        $this->site_tagline = setting('site_tagline', '');
        $this->site_logo_url = setting('site_logo');
        $this->site_favicon_url = setting('site_favicon');
        $this->admin_email = setting('admin_email', '');

        $this->timezone = setting('timezone', 'Asia/Jakarta');
        $this->date_format = setting('date_format', 'd M Y');
        $this->time_format = setting('time_format', 'H:i');

        $this->homepage_type = setting('homepage_type', 'page');
        $this->homepage_page_id = setting('homepage_page_id');
        $this->posts_page_id = setting('posts_page_id');
        $this->items_per_page = setting('items_per_page', 10);

        $this->maintenance_mode = setting('maintenance_mode', false);
        $this->maintenance_message = setting('maintenance_message', '');
        $this->maintenance_allowed_ips = setting('maintenance_allowed_ips', '');
    }

    public function save()
    {
        $this->validate([
            'site_name' => 'required|string|max:255',
            'admin_email' => 'nullable|email',
            'items_per_page' => 'required|integer|min:1|max:100',
        ]);

        // Handle file uploads
        if ($this->site_logo) {
            $path = $this->site_logo->store('settings', 'public');
            $this->site_logo_url = '/storage/' . $path;
        }

        if ($this->site_favicon) {
            $path = $this->site_favicon->store('settings', 'public');
            $this->site_favicon_url = '/storage/' . $path;
        }

        // Save all settings
        Setting::setMany([
            'site_name' => $this->site_name,
            'site_tagline' => $this->site_tagline,
            'site_logo' => $this->site_logo_url,
            'site_favicon' => $this->site_favicon_url,
            'admin_email' => $this->admin_email,
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'homepage_type' => $this->homepage_type,
            'homepage_page_id' => $this->homepage_page_id,
            'posts_page_id' => $this->posts_page_id,
            'items_per_page' => $this->items_per_page,
            'maintenance_mode' => $this->maintenance_mode,
            'maintenance_message' => $this->maintenance_message,
            'maintenance_allowed_ips' => $this->maintenance_allowed_ips,
        ], 'general');

        // Clear config cache
        Setting::clearCache();

        session()->flash('message', 'Settings saved successfully!');
    }

    public function removeLogo()
    {
        $this->site_logo_url = null;
        $this->site_logo = null;
    }

    public function removeFavicon()
    {
        $this->site_favicon_url = null;
        $this->site_favicon = null;
    }

    public function render()
    {
        return view('livewire.admin.settings.general-settings', [
            'pages' => Page::where('status', 'published')->orderBy('title')->get(),
            'timezones' => timezone_identifiers_list(),
            'dateFormats' => [
                'd M Y' => date('d M Y'),
                'd/m/Y' => date('d/m/Y'),
                'm/d/Y' => date('m/d/Y'),
                'Y-m-d' => date('Y-m-d'),
                'F j, Y' => date('F j, Y'),
            ],
            'timeFormats' => [
                'H:i' => date('H:i') . ' (24-hour)',
                'h:i A' => date('h:i A') . ' (12-hour)',
            ],
        ]);
    }
}
```

#### 9. Database Migration

```php
// database/migrations/xxxx_create_settings_table.php

Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('group')->default('general');
    $table->string('type')->default('string');
    $table->timestamps();

    $table->index('group');
});
```

#### 10. Database Seeder - Default Settings

```php
// database/seeders/SettingsSeeder.php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            // General
            ['key' => 'site_name', 'value' => 'Web CMS', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_tagline', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'group' => 'general', 'type' => 'string'],
            ['key' => 'date_format', 'value' => 'd M Y', 'group' => 'general', 'type' => 'string'],
            ['key' => 'time_format', 'value' => 'H:i', 'group' => 'general', 'type' => 'string'],
            ['key' => 'homepage_type', 'value' => 'page', 'group' => 'general', 'type' => 'string'],
            ['key' => 'items_per_page', 'value' => '10', 'group' => 'general', 'type' => 'integer'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'general', 'type' => 'boolean'],

            // SEO
            ['key' => 'meta_title_separator', 'value' => '|', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'sitemap_enabled', 'value' => 'true', 'group' => 'seo', 'type' => 'boolean'],

            // LiteSpeed Cache
            ['key' => 'lscache_enabled', 'value' => 'false', 'group' => 'lscache', 'type' => 'boolean'],
        ];

        foreach ($defaults as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
```

#### 11. File yang Perlu Dibuat untuk General Settings

1. `app/Models/Setting.php`
2. `app/helpers.php`
3. `app/Http/Middleware/CheckMaintenanceMode.php`
4. `app/Livewire/Admin/Settings/GeneralSettings.php`
5. `database/migrations/xxxx_create_settings_table.php`
6. `database/seeders/SettingsSeeder.php`
7. `resources/views/livewire/admin/settings/general-settings.blade.php`
8. `resources/views/maintenance.blade.php`

---

## 2. Settings > Brevo API

**Untuk integrasi email marketing dengan Brevo (Sendinblue):**

### API Configuration
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `brevo_enabled` | boolean | false | Aktifkan integrasi Brevo |
| `brevo_api_key` | password | '' | API Key dari Brevo |
| `brevo_sender_name` | string | '' | Nama pengirim email |
| `brevo_sender_email` | email | '' | Email pengirim |

### List & Templates
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `brevo_default_list_id` | number | null | Default list ID untuk subscriber |
| `brevo_double_optin` | boolean | true | Gunakan double opt-in |
| `brevo_welcome_template_id` | number | null | Template ID untuk welcome email |
| `brevo_confirmation_template_id` | number | null | Template ID untuk konfirmasi |

### Transactional Email
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `brevo_transactional_enabled` | boolean | false | Gunakan Brevo untuk transactional email |
| `brevo_contact_form_template_id` | number | null | Template untuk notifikasi contact form |

### Sync Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `brevo_sync_users` | boolean | false | Sync user CMS ke Brevo |
| `brevo_user_list_id` | number | null | List ID untuk sync users |

---

### Implementasi Brevo dari Hulu ke Hilir

#### 1. Database Schema

##### Tabel `newsletter_subscribers`
```php
Schema::create('newsletter_subscribers', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('name')->nullable();
    $table->string('status')->default('pending'); // pending, subscribed, unsubscribed
    $table->string('brevo_contact_id')->nullable(); // ID dari Brevo
    $table->string('confirmation_token')->nullable();
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamp('unsubscribed_at')->nullable();
    $table->string('source')->nullable(); // form_id, popup, footer, etc.
    $table->json('lists')->nullable(); // Brevo list IDs
    $table->json('attributes')->nullable(); // Custom attributes
    $table->string('ip_address')->nullable();
    $table->string('language_code', 10)->default('en');
    $table->timestamps();
});
```

##### Tabel `newsletter_campaigns` (Opsional - jika ingin manage campaign dari CMS)
```php
Schema::create('newsletter_campaigns', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('subject');
    $table->text('content');
    $table->string('brevo_campaign_id')->nullable();
    $table->string('status')->default('draft'); // draft, scheduled, sent
    $table->timestamp('scheduled_at')->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->json('stats')->nullable(); // opens, clicks, etc.
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});
```

#### 2. Service Class - BrevoService.php

```php
namespace App\Services;

use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateContact;
use Brevo\Client\Model\SendSmtpEmail;

class BrevoService
{
    protected ?ContactsApi $contactsApi = null;
    protected ?TransactionalEmailsApi $emailApi = null;

    public function __construct()
    {
        if (!setting('brevo_enabled') || !setting('brevo_api_key')) {
            return;
        }

        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', setting('brevo_api_key'));

        $this->contactsApi = new ContactsApi(null, $config);
        $this->emailApi = new TransactionalEmailsApi(null, $config);
    }

    public function isEnabled(): bool
    {
        return setting('brevo_enabled') && setting('brevo_api_key');
    }

    // ========== CONTACTS ==========

    public function createContact(string $email, array $attributes = [], array $listIds = []): ?string
    {
        if (!$this->contactsApi) return null;

        $contact = new CreateContact([
            'email' => $email,
            'attributes' => $attributes,
            'listIds' => $listIds ?: [setting('brevo_default_list_id')],
            'updateEnabled' => true,
        ]);

        try {
            $result = $this->contactsApi->createContact($contact);
            return $result->getId();
        } catch (\Exception $e) {
            logger()->error('Brevo createContact error: ' . $e->getMessage());
            return null;
        }
    }

    public function updateContact(string $email, array $attributes = []): bool
    {
        if (!$this->contactsApi) return false;

        try {
            $this->contactsApi->updateContact($email, ['attributes' => $attributes]);
            return true;
        } catch (\Exception $e) {
            logger()->error('Brevo updateContact error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteContact(string $email): bool
    {
        if (!$this->contactsApi) return false;

        try {
            $this->contactsApi->deleteContact($email);
            return true;
        } catch (\Exception $e) {
            logger()->error('Brevo deleteContact error: ' . $e->getMessage());
            return false;
        }
    }

    public function addToList(string $email, int $listId): bool
    {
        if (!$this->contactsApi) return false;

        try {
            $this->contactsApi->addContactToList($listId, ['emails' => [$email]]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function removeFromList(string $email, int $listId): bool
    {
        if (!$this->contactsApi) return false;

        try {
            $this->contactsApi->removeContactFromList($listId, ['emails' => [$email]]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ========== TRANSACTIONAL EMAILS ==========

    public function sendTransactionalEmail(
        string $to,
        int $templateId,
        array $params = [],
        ?string $toName = null
    ): bool {
        if (!$this->emailApi || !setting('brevo_transactional_enabled')) {
            return false;
        }

        $email = new SendSmtpEmail([
            'to' => [['email' => $to, 'name' => $toName]],
            'templateId' => $templateId,
            'params' => $params,
        ]);

        try {
            $this->emailApi->sendTransacEmail($email);
            return true;
        } catch (\Exception $e) {
            logger()->error('Brevo sendEmail error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendConfirmationEmail(string $email, string $token): bool
    {
        $templateId = setting('brevo_confirmation_template_id');
        if (!$templateId) return false;

        return $this->sendTransactionalEmail($email, $templateId, [
            'confirmation_link' => route('newsletter.confirm', $token),
        ]);
    }

    public function sendWelcomeEmail(string $email, ?string $name = null): bool
    {
        $templateId = setting('brevo_welcome_template_id');
        if (!$templateId) return false;

        return $this->sendTransactionalEmail($email, $templateId, [
            'name' => $name ?? 'Subscriber',
        ], $name);
    }

    // ========== LISTS ==========

    public function getLists(): array
    {
        if (!$this->contactsApi) return [];

        try {
            $result = $this->contactsApi->getLists();
            return $result->getLists() ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // ========== TEST CONNECTION ==========

    public function testConnection(): array
    {
        if (!$this->contactsApi) {
            return ['success' => false, 'message' => 'API not configured'];
        }

        try {
            $account = (new \Brevo\Client\Api\AccountApi(null,
                Configuration::getDefaultConfiguration()->setApiKey('api-key', setting('brevo_api_key'))
            ))->getAccount();

            return [
                'success' => true,
                'message' => 'Connected successfully',
                'account' => [
                    'email' => $account->getEmail(),
                    'company' => $account->getCompanyName(),
                    'plan' => $account->getPlan()[0]->getType() ?? 'Unknown',
                ],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
```

#### 3. Model - NewsletterSubscriber.php

```php
namespace App\Models;

use App\Services\BrevoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email', 'name', 'status', 'brevo_contact_id', 'confirmation_token',
        'confirmed_at', 'unsubscribed_at', 'source', 'lists', 'attributes',
        'ip_address', 'language_code',
    ];

    protected $casts = [
        'lists' => 'array',
        'attributes' => 'array',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    // ========== EVENTS ==========

    protected static function booted()
    {
        // Sync to Brevo on create
        static::created(function ($subscriber) {
            if ($subscriber->status === 'subscribed') {
                $subscriber->syncToBrevo();
            }
        });

        // Sync updates to Brevo
        static::updated(function ($subscriber) {
            if ($subscriber->isDirty('status')) {
                if ($subscriber->status === 'subscribed') {
                    $subscriber->syncToBrevo();
                } elseif ($subscriber->status === 'unsubscribed') {
                    $subscriber->removeFromBrevo();
                }
            }
        });
    }

    // ========== METHODS ==========

    public function syncToBrevo(): bool
    {
        $brevo = app(BrevoService::class);
        if (!$brevo->isEnabled()) return false;

        $contactId = $brevo->createContact($this->email, [
            'FIRSTNAME' => $this->name,
            'LANGUAGE' => $this->language_code,
            'SOURCE' => $this->source,
        ], $this->lists ?? []);

        if ($contactId) {
            $this->update(['brevo_contact_id' => $contactId]);
            return true;
        }

        return false;
    }

    public function removeFromBrevo(): bool
    {
        $brevo = app(BrevoService::class);
        return $brevo->deleteContact($this->email);
    }

    public static function subscribe(string $email, ?string $name = null, ?string $source = null): self
    {
        $doubleOptin = setting('brevo_double_optin', true);

        $subscriber = self::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'status' => $doubleOptin ? 'pending' : 'subscribed',
                'confirmation_token' => $doubleOptin ? Str::random(64) : null,
                'source' => $source,
                'ip_address' => request()->ip(),
                'language_code' => app()->getLocale(),
                'confirmed_at' => $doubleOptin ? null : now(),
            ]
        );

        if ($doubleOptin && $subscriber->wasRecentlyCreated) {
            app(BrevoService::class)->sendConfirmationEmail($email, $subscriber->confirmation_token);
        } elseif (!$doubleOptin) {
            app(BrevoService::class)->sendWelcomeEmail($email, $name);
        }

        return $subscriber;
    }

    public function confirm(): bool
    {
        if ($this->status !== 'pending') return false;

        $this->update([
            'status' => 'subscribed',
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);

        app(BrevoService::class)->sendWelcomeEmail($this->email, $this->name);

        return true;
    }

    public function unsubscribe(): bool
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return true;
    }
}
```

#### 4. Integrasi dengan User Model

```php
// app/Models/User.php

class User extends Authenticatable
{
    // ... existing code

    protected static function booted()
    {
        // Sync user ke Brevo saat register
        static::created(function ($user) {
            if (setting('brevo_sync_users') && setting('brevo_user_list_id')) {
                app(BrevoService::class)->createContact(
                    $user->email,
                    [
                        'FIRSTNAME' => $user->name,
                        'CMS_USER_ID' => $user->id,
                        'ROLE' => $user->roles->first()?->name ?? 'user',
                    ],
                    [setting('brevo_user_list_id')]
                );
            }
        });

        // Update di Brevo saat user update
        static::updated(function ($user) {
            if (setting('brevo_sync_users') && $user->isDirty(['name', 'email'])) {
                app(BrevoService::class)->updateContact($user->getOriginal('email'), [
                    'FIRSTNAME' => $user->name,
                    'EMAIL' => $user->email,
                ]);
            }
        });
    }
}
```

#### 5. Page Block: Newsletter Form

Tambahkan block type baru untuk newsletter di Page Builder:

```php
// Di config atau database page_blocks
$blockTypes = [
    // ... existing blocks
    'newsletter' => [
        'name' => 'Newsletter Subscription',
        'icon' => 'mail',
        'fields' => [
            'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Subscribe to our newsletter'],
            'description' => ['type' => 'textarea', 'label' => 'Description'],
            'button_text' => ['type' => 'text', 'label' => 'Button Text', 'default' => 'Subscribe'],
            'success_message' => ['type' => 'text', 'label' => 'Success Message', 'default' => 'Thank you for subscribing!'],
            'list_id' => ['type' => 'select', 'label' => 'Brevo List', 'options' => 'brevo_lists'],
            'show_name_field' => ['type' => 'switcher', 'label' => 'Show Name Field', 'default' => true],
            'style' => ['type' => 'select', 'label' => 'Style', 'options' => ['inline', 'stacked', 'card']],
        ],
    ],
];
```

#### 6. Livewire Component - NewsletterForm.php

```php
namespace App\Livewire;

use App\Models\NewsletterSubscriber;
use Livewire\Component;

class NewsletterForm extends Component
{
    public string $email = '';
    public string $name = '';
    public bool $showNameField = true;
    public ?int $listId = null;
    public string $source = 'website';

    public bool $submitted = false;
    public string $message = '';

    protected $rules = [
        'email' => 'required|email',
        'name' => 'nullable|string|max:255',
    ];

    public function subscribe()
    {
        $this->validate();

        try {
            $subscriber = NewsletterSubscriber::subscribe(
                $this->email,
                $this->showNameField ? $this->name : null,
                $this->source
            );

            if ($this->listId) {
                $subscriber->update(['lists' => [$this->listId]]);
            }

            $this->submitted = true;
            $this->message = setting('brevo_double_optin')
                ? 'Please check your email to confirm your subscription.'
                : 'Thank you for subscribing!';

        } catch (\Exception $e) {
            $this->addError('email', 'Failed to subscribe. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.newsletter-form');
    }
}
```

#### 7. Routes untuk Newsletter

```php
// routes/web.php
Route::get('/newsletter/confirm/{token}', function ($token) {
    $subscriber = NewsletterSubscriber::where('confirmation_token', $token)->first();

    if (!$subscriber) {
        return redirect('/')->with('error', 'Invalid confirmation link.');
    }

    $subscriber->confirm();

    return redirect('/')->with('success', 'Your subscription has been confirmed!');
})->name('newsletter.confirm');

Route::get('/newsletter/unsubscribe/{email}', function ($email) {
    $subscriber = NewsletterSubscriber::where('email', $email)->first();

    if ($subscriber) {
        $subscriber->unsubscribe();
    }

    return view('newsletter.unsubscribed');
})->name('newsletter.unsubscribe');
```

#### 8. Admin Panel - Subscribers Management

```php
// routes/admin.php
Route::prefix('newsletter')->name('admin.newsletter.')->group(function () {
    Route::get('/subscribers', [NewsletterController::class, 'subscribers'])->name('subscribers');
    Route::get('/campaigns', [NewsletterController::class, 'campaigns'])->name('campaigns');
    Route::post('/sync-all', [NewsletterController::class, 'syncAll'])->name('sync-all');
    Route::post('/export', [NewsletterController::class, 'export'])->name('export');
});
```

#### 9. File yang Perlu Dibuat untuk Brevo

1. `app/Services/BrevoService.php`
2. `app/Models/NewsletterSubscriber.php`
3. `app/Livewire/NewsletterForm.php`
4. `app/Http/Controllers/Admin/NewsletterController.php`
5. `app/Livewire/Admin/Newsletter/SubscribersTable.php`
6. `database/migrations/create_newsletter_subscribers_table.php`
7. `resources/views/livewire/newsletter-form.blade.php`
8. `resources/views/newsletter/unsubscribed.blade.php`
9. Page Block template untuk newsletter

#### 10. Composer Package
```bash
composer require getbrevo/brevo-php
```

---

## 3. Settings > Languages

**Untuk multi-language support:**

### Default Language
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `default_language` | select | 'en' | Bahasa default website |
| `admin_language` | select | 'en' | Bahasa admin panel |
| `fallback_language` | select | 'en' | Bahasa fallback |

### Available Languages
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `enabled_languages` | multi-select | ['en'] | Bahasa yang diaktifkan |
| `language_in_url` | boolean | true | Tampilkan kode bahasa di URL |
| `hide_default_language` | boolean | true | Sembunyikan bahasa default di URL |

### Language Detection
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `detect_browser_language` | boolean | false | Deteksi bahasa browser |
| `language_cookie_name` | string | 'locale' | Nama cookie untuk bahasa |
| `language_cookie_duration` | number | 365 | Durasi cookie (hari) |

### Translation Options
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `auto_translate` | boolean | false | Auto translate dengan API |
| `translation_api` | select | 'google' | API translator (google/deepl) |
| `translation_api_key` | password | '' | API key untuk translator |

---

### Implementasi Multi-Language di Konten

#### Pendekatan: Translation Groups

Menggunakan sistem **translation group** dimana setiap konten yang sama (di bahasa berbeda) dihubungkan dalam satu grup.

#### Tabel Database: `languages`
```php
Schema::create('languages', function (Blueprint $table) {
    $table->id();
    $table->string('code', 10)->unique(); // en, id, zh-CN
    $table->string('name'); // English, Indonesian, Chinese
    $table->string('native_name'); // English, Bahasa Indonesia, 中文
    $table->string('flag')->nullable(); // emoji atau path ke flag icon
    $table->boolean('is_active')->default(true);
    $table->boolean('is_rtl')->default(false); // Right-to-left
    $table->integer('order')->default(0);
    $table->timestamps();
});
```

#### Tabel Database: `translations`
```php
Schema::create('translations', function (Blueprint $table) {
    $table->id();
    $table->uuid('translation_group'); // UUID untuk mengelompokkan terjemahan
    $table->morphs('translatable'); // translatable_type, translatable_id
    $table->string('language_code', 10);
    $table->timestamps();

    $table->unique(['translatable_type', 'translatable_id']);
    $table->index(['translation_group', 'language_code']);
    $table->foreign('language_code')->references('code')->on('languages');
});
```

---

### Implementasi di Pages

#### Update Tabel `pages`
```php
// Tambah kolom di migration
$table->string('language_code', 10)->default('en');
$table->foreign('language_code')->references('code')->on('languages');
```

#### Model `Page.php` - Tambah Trait
```php
use App\Traits\HasTranslations;

class Page extends Model
{
    use HasTranslations;

    protected $fillable = [
        // ... existing fields
        'language_code',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }
}
```

#### Trait `HasTranslations.php`
```php
namespace App\Traits;

use App\Models\Translation;

trait HasTranslations
{
    public function translation()
    {
        return $this->morphOne(Translation::class, 'translatable');
    }

    public function getTranslationGroup(): ?string
    {
        return $this->translation?->translation_group;
    }

    public function getTranslations()
    {
        if (!$this->translation) return collect([$this]);

        return Translation::where('translation_group', $this->translation->translation_group)
            ->with('translatable')
            ->get()
            ->pluck('translatable');
    }

    public function getTranslation(string $languageCode)
    {
        if ($this->language_code === $languageCode) return $this;

        if (!$this->translation) return null;

        return Translation::where('translation_group', $this->translation->translation_group)
            ->where('language_code', $languageCode)
            ->first()?->translatable;
    }

    public function hasTranslation(string $languageCode): bool
    {
        return $this->getTranslation($languageCode) !== null;
    }

    public function linkTranslation(Model $translatedModel): void
    {
        $group = $this->translation?->translation_group ?? Str::uuid()->toString();

        // Create/update translation for original
        Translation::updateOrCreate(
            ['translatable_type' => get_class($this), 'translatable_id' => $this->id],
            ['translation_group' => $group, 'language_code' => $this->language_code]
        );

        // Create/update translation for translated model
        Translation::updateOrCreate(
            ['translatable_type' => get_class($translatedModel), 'translatable_id' => $translatedModel->id],
            ['translation_group' => $group, 'language_code' => $translatedModel->language_code]
        );
    }
}
```

#### UI di Admin - PageForm.php
```php
// Tambah di form
public string $language_code = 'en';
public ?string $translationOf = null; // ID halaman yang diterjemahkan

// Di view, tambah:
// 1. Language selector dropdown
// 2. "Translate to" button jika belum ada terjemahan
// 3. List of available translations dengan link
```

#### Tampilan di Admin (Blade)
```html
<!-- Language Selector -->
<div class="flex items-center gap-2 mb-4">
    <label>Language:</label>
    <select wire:model="language_code">
        @foreach($languages as $lang)
            <option value="{{ $lang->code }}">{{ $lang->flag }} {{ $lang->name }}</option>
        @endforeach
    </select>
</div>

<!-- Translation Links -->
@if($page->exists && $translations->count() > 0)
<div class="flex gap-2">
    <span>Translations:</span>
    @foreach($translations as $translation)
        <a href="{{ route('admin.pages.edit', $translation->id) }}"
           class="{{ $translation->id === $page->id ? 'font-bold' : '' }}">
            {{ $translation->language->flag }} {{ $translation->language->code }}
        </a>
    @endforeach

    @foreach($missingLanguages as $lang)
        <a href="{{ route('admin.pages.create', ['translate_from' => $page->id, 'language' => $lang->code]) }}"
           class="text-gray-400">
            + {{ $lang->flag }} {{ $lang->code }}
        </a>
    @endforeach
</div>
@endif
```

---

### Implementasi di CPT (Custom Post Types)

#### Update Tabel `cpt_entries`
```php
// Tambah kolom di migration
$table->string('language_code', 10)->default('en');
$table->foreign('language_code')->references('code')->on('languages');
```

#### Model `CptEntry.php`
```php
use App\Traits\HasTranslations;

class CptEntry extends Model
{
    use HasTranslations;

    protected $fillable = [
        // ... existing fields
        'language_code',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }
}
```

#### UI di Admin - EntryForm.php
Sama seperti PageForm, tambahkan:
- Language selector
- Translation links
- "Create translation" button

---

### Implementasi di Plugin Posts

#### Update Tabel `posts` (di plugin)
```php
// Migration di plugins/posts/database/migrations/
$table->string('language_code', 10)->default('en');
$table->foreign('language_code')->references('code')->on('languages');
```

#### Model `Post.php` (di plugin)
```php
use App\Traits\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    protected $fillable = [
        // ... existing fields
        'language_code',
    ];

    public function language()
    {
        return $this->belongsTo(\App\Models\Language::class, 'language_code', 'code');
    }
}
```

#### Update PostForm.php Livewire
```php
public string $language_code = 'en';
public ?int $translateFrom = null;

public function mount($post = null, $translateFrom = null, $language = null)
{
    if ($translateFrom && $language) {
        $original = Post::findOrFail($translateFrom);
        $this->translateFrom = $translateFrom;
        $this->language_code = $language;

        // Pre-fill dengan data original (optional)
        $this->title = $original->title . ' [' . strtoupper($language) . ']';
        $this->content = $original->content;
        // ... copy other fields
    }
}

public function save()
{
    // ... existing save logic

    // Link translation jika ini adalah terjemahan
    if ($this->translateFrom) {
        $original = Post::find($this->translateFrom);
        $original->linkTranslation($this->post);
    }
}
```

---

### Implementasi di Taxonomies (Categories, Tags)

#### Update Tabel `taxonomy_terms`
```php
$table->string('language_code', 10)->default('en');
$table->foreign('language_code')->references('code')->on('languages');
```

#### Model `TaxonomyTerm.php`
```php
use App\Traits\HasTranslations;

class TaxonomyTerm extends Model
{
    use HasTranslations;
    // ... sama seperti di atas
}
```

---

### Frontend URL Routing

#### Middleware `SetLocale.php`
```php
namespace App\Http\Middleware;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $this->determineLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    protected function determineLocale($request): string
    {
        // 1. Check URL prefix: /en/about, /id/tentang
        $segment = $request->segment(1);
        if ($segment && Language::where('code', $segment)->where('is_active', true)->exists()) {
            return $segment;
        }

        // 2. Check cookie
        if ($cookie = $request->cookie(setting('language_cookie_name', 'locale'))) {
            if (Language::where('code', $cookie)->where('is_active', true)->exists()) {
                return $cookie;
            }
        }

        // 3. Check browser language (if enabled)
        if (setting('detect_browser_language')) {
            $browserLang = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            if (Language::where('code', $browserLang)->where('is_active', true)->exists()) {
                return $browserLang;
            }
        }

        // 4. Return default
        return setting('default_language', 'en');
    }
}
```

#### Routes untuk Multi-Language
```php
// routes/web.php

// Jika language_in_url = true
Route::group(['prefix' => '{locale?}', 'middleware' => ['set-locale']], function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');
    // ... other routes
});

// Atau pattern berdasarkan setting
if (setting('language_in_url')) {
    Route::prefix('{locale}')->group(function () {
        // routes dengan locale prefix
    });
} else {
    // routes tanpa locale prefix
}
```

#### PageController - Frontend
```php
public function show(Request $request, string $slug, ?string $locale = null)
{
    $locale = $locale ?? app()->getLocale();

    $page = Page::where('slug', $slug)
        ->where('language_code', $locale)
        ->where('status', 'published')
        ->first();

    // Fallback ke default language jika tidak ada
    if (!$page && setting('fallback_language')) {
        $page = Page::where('slug', $slug)
            ->where('language_code', setting('fallback_language'))
            ->where('status', 'published')
            ->first();
    }

    if (!$page) {
        abort(404);
    }

    return view('pages.show', compact('page'));
}
```

---

### Language Switcher Component

#### Blade Component
```html
<!-- resources/views/components/language-switcher.blade.php -->
@php
    $languages = \App\Models\Language::where('is_active', true)->orderBy('order')->get();
    $currentLocale = app()->getLocale();
    $currentPage = request()->route()->parameter('page') ?? null;
@endphp

<div class="language-switcher">
    @foreach($languages as $lang)
        @php
            // Cari translation dari halaman saat ini
            $translatedUrl = null;
            if ($currentPage && $currentPage->hasTranslation($lang->code)) {
                $translated = $currentPage->getTranslation($lang->code);
                $translatedUrl = route('page.show', ['locale' => $lang->code, 'slug' => $translated->slug]);
            } else {
                // Default ke homepage bahasa tersebut
                $translatedUrl = route('home', ['locale' => $lang->code]);
            }
        @endphp

        <a href="{{ $translatedUrl }}"
           class="{{ $currentLocale === $lang->code ? 'active' : '' }}"
           @if($currentLocale !== $lang->code) hreflang="{{ $lang->code }}" @endif>
            {{ $lang->flag }} {{ $lang->native_name }}
        </a>
    @endforeach
</div>
```

---

### Admin - Language Management Page

#### Fitur di Settings > Languages
1. **List Languages** - CRUD untuk mengelola bahasa
2. **Reorder Languages** - Drag & drop untuk urutan
3. **Set Default** - Pilih bahasa default
4. **Translation Status Dashboard**:
   - Pages: 10/15 translated to ID
   - Posts: 25/30 translated to ID
   - Categories: 5/5 translated to ID

#### Tabel di Admin
| Content Type | EN (Default) | ID | ZH | Actions |
|--------------|--------------|----|----|---------|
| Pages | 15 | 10 (67%) | 5 (33%) | View Missing |
| Posts | 30 | 25 (83%) | 10 (33%) | View Missing |
| Categories | 5 | 5 (100%) | 3 (60%) | View Missing |

---

### File Implementasi untuk Languages

#### File yang Perlu Dibuat:
1. `app/Models/Language.php`
2. `app/Models/Translation.php`
3. `app/Traits/HasTranslations.php`
4. `app/Http/Middleware/SetLocale.php`
5. `app/Livewire/Admin/Settings/LanguageSettings.php`
6. `app/Livewire/Admin/Languages/LanguageManager.php` (CRUD)
7. `resources/views/components/language-switcher.blade.php`
8. Migrations: `create_languages_table`, `create_translations_table`
9. Update migrations: `add_language_code_to_pages`, `add_language_code_to_cpt_entries`
10. Plugin Posts: `add_language_code_to_posts`

#### Routes untuk Language Management
```php
Route::prefix('settings/languages')->name('admin.languages.')->group(function () {
    Route::get('/', [LanguageController::class, 'index'])->name('index');
    Route::post('/', [LanguageController::class, 'store'])->name('store');
    Route::put('/{language}', [LanguageController::class, 'update'])->name('update');
    Route::delete('/{language}', [LanguageController::class, 'destroy'])->name('destroy');
    Route::post('/reorder', [LanguageController::class, 'reorder'])->name('reorder');
});
```

---

## 4. Settings > SEO

**Untuk Search Engine Optimization:**

### Global Meta Tags
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `meta_title_suffix` | string | ' | Site Name' | Suffix untuk semua title |
| `meta_title_separator` | select | '|' | Separator title (|, -, :) |
| `default_meta_description` | textarea | '' | Meta description default |
| `default_meta_keywords` | text | '' | Keywords default (deprecated tapi masih dipakai) |

### Social Media / Open Graph
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `og_default_image` | media | null | Default OG image |
| `og_image_width` | number | 1200 | Lebar OG image |
| `og_image_height` | number | 630 | Tinggi OG image |
| `twitter_card_type` | select | 'summary_large_image' | Tipe Twitter card |
| `twitter_site` | string | '' | Twitter handle (@username) |

### Verification & Analytics
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `google_site_verification` | string | '' | Google verification code |
| `bing_site_verification` | string | '' | Bing verification code |
| `google_analytics_id` | string | '' | Google Analytics ID (GA4) |
| `google_tag_manager_id` | string | '' | GTM container ID |

### Sitemap
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `sitemap_enabled` | boolean | true | Generate sitemap.xml |
| `sitemap_include_pages` | boolean | true | Include pages di sitemap |
| `sitemap_include_posts` | boolean | true | Include posts di sitemap |
| `sitemap_include_taxonomies` | boolean | true | Include taxonomies |
| `sitemap_changefreq` | select | 'weekly' | Default change frequency |
| `sitemap_priority` | number | 0.5 | Default priority |

### Robots.txt
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `robots_txt_content` | textarea | 'User-agent: *\nAllow: /' | Konten robots.txt |
| `noindex_archives` | boolean | false | Noindex archive pages |
| `noindex_search` | boolean | true | Noindex search results |

### Schema / Structured Data
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `schema_organization_name` | string | '' | Nama organisasi untuk schema |
| `schema_organization_logo` | media | null | Logo untuk schema |
| `schema_social_profiles` | repeater | [] | Social media URLs |

---

### Implementasi SEO dari Hulu ke Hilir

#### 1. Trait HasSeo untuk Model

```php
namespace App\Traits;

trait HasSeo
{
    public function initializeSeoFields(): void
    {
        $this->mergeFillable(['seo']);
        $this->mergeCasts(['seo' => 'array']);
    }

    // ========== GETTERS ==========

    public function getMetaTitle(): string
    {
        $title = $this->seo['meta_title'] ?? $this->title ?? '';
        $suffix = setting('meta_title_suffix', '');
        $separator = setting('meta_title_separator', '|');

        return $title . ($suffix ? " {$separator} {$suffix}" : '');
    }

    public function getMetaDescription(): ?string
    {
        return $this->seo['meta_description']
            ?? $this->excerpt
            ?? setting('default_meta_description');
    }

    public function getMetaKeywords(): ?string
    {
        return $this->seo['meta_keywords'] ?? setting('default_meta_keywords');
    }

    public function getCanonicalUrl(): string
    {
        return $this->seo['canonical_url'] ?? $this->getUrl();
    }

    public function getOgTitle(): string
    {
        return $this->seo['og_title'] ?? $this->getMetaTitle();
    }

    public function getOgDescription(): ?string
    {
        return $this->seo['og_description'] ?? $this->getMetaDescription();
    }

    public function getOgImage(): ?string
    {
        if (!empty($this->seo['og_image'])) {
            return $this->seo['og_image'];
        }

        if ($this->featured_image) {
            return $this->featured_image;
        }

        return setting('og_default_image');
    }

    public function getRobots(): string
    {
        $index = ($this->seo['noindex'] ?? false) ? 'noindex' : 'index';
        $follow = ($this->seo['nofollow'] ?? false) ? 'nofollow' : 'follow';

        return "{$index}, {$follow}";
    }

    public function getSitemapPriority(): float
    {
        return $this->seo['sitemap_priority'] ?? setting('sitemap_priority', 0.5);
    }

    public function getSitemapChangefreq(): string
    {
        return $this->seo['sitemap_changefreq'] ?? setting('sitemap_changefreq', 'weekly');
    }

    public function shouldExcludeFromSitemap(): bool
    {
        return $this->seo['exclude_from_sitemap'] ?? false;
    }

    // ========== SCHEMA ==========

    public function getSchemaType(): string
    {
        return $this->seo['schema_type'] ?? 'WebPage';
    }

    public function toSchemaArray(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => $this->getSchemaType(),
            'name' => $this->title,
            'description' => $this->getMetaDescription(),
            'url' => $this->getCanonicalUrl(),
            'image' => $this->getOgImage(),
            'datePublished' => $this->created_at?->toIso8601String(),
            'dateModified' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

#### 2. Implementasi di Page Model

```php
// app/Models/Page.php

use App\Traits\HasSeo;

class Page extends Model
{
    use HasSeo;

    protected $fillable = [
        'title', 'slug', 'content', 'status', 'template',
        'parent_id', 'featured_image', 'menu_order', 'author_id',
        'seo', // JSON field untuk semua SEO data
        'language_code',
    ];

    protected $casts = [
        'seo' => 'array',
    ];

    // Default SEO structure
    public static function defaultSeo(): array
    {
        return [
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'canonical_url' => '',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'twitter_title' => '',
            'twitter_description' => '',
            'twitter_image' => '',
            'noindex' => false,
            'nofollow' => false,
            'exclude_from_sitemap' => false,
            'sitemap_priority' => 0.5,
            'sitemap_changefreq' => 'weekly',
            'schema_type' => 'WebPage',
            'focus_keyword' => '',
        ];
    }

    public function getUrl(): string
    {
        $locale = $this->language_code;
        $hideDefault = setting('hide_default_language', true);

        if ($hideDefault && $locale === setting('default_language')) {
            return url($this->slug);
        }

        return url("{$locale}/{$this->slug}");
    }
}
```

#### 3. Implementasi di CPT Entry Model

```php
// app/Models/CptEntry.php

use App\Traits\HasSeo;

class CptEntry extends Model
{
    use HasSeo;

    protected $fillable = [
        'custom_post_type_id', 'title', 'slug', 'content', 'excerpt',
        'status', 'featured_image', 'author_id', 'published_at',
        'seo', 'language_code', 'meta_fields',
    ];

    protected $casts = [
        'seo' => 'array',
        'meta_fields' => 'array',
    ];

    public function getUrl(): string
    {
        $cpt = $this->customPostType;
        $locale = $this->language_code;

        return url("{$locale}/{$cpt->slug}/{$this->slug}");
    }

    public function toSchemaArray(): array
    {
        $base = parent::toSchemaArray();

        // Override type berdasarkan CPT
        $schemaTypes = [
            'products' => 'Product',
            'events' => 'Event',
            'team' => 'Person',
            'testimonials' => 'Review',
            'faq' => 'FAQPage',
        ];

        $base['@type'] = $schemaTypes[$this->customPostType->slug] ?? 'Article';

        return $base;
    }
}
```

#### 4. Implementasi di Posts Plugin

```php
// plugins/posts/src/Models/Post.php

use App\Traits\HasSeo;

class Post extends Model
{
    use HasSeo;

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'status',
        'featured_image', 'author_id', 'published_at',
        'seo', 'language_code',
    ];

    protected $casts = [
        'seo' => 'array',
        'published_at' => 'datetime',
    ];

    public function getUrl(): string
    {
        $archiveSlug = setting('posts.archive_slug', 'blog');
        $locale = $this->language_code;

        return url("{$locale}/{$archiveSlug}/{$this->slug}");
    }

    public function toSchemaArray(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $this->title,
            'description' => $this->getMetaDescription(),
            'url' => $this->getCanonicalUrl(),
            'image' => $this->getOgImage(),
            'datePublished' => $this->published_at?->toIso8601String(),
            'dateModified' => $this->updated_at?->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $this->author?->name,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => setting('schema_organization_name', setting('site_name')),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => setting('schema_organization_logo'),
                ],
            ],
        ];
    }
}
```

#### 5. SEO Section di Admin Forms

```php
// Komponen Livewire untuk SEO di PageForm, EntryForm, PostForm

// Properties
public array $seo = [];
public bool $showSeoPanel = false;
public ?int $seoScore = null;
public array $seoAnalysis = [];

public function mount($model = null)
{
    $this->seo = $model?->seo ?? Page::defaultSeo();
    $this->analyzeSeo();
}

public function updatedSeo($value, $key)
{
    $this->analyzeSeo();
}

public function analyzeSeo(): void
{
    $issues = [];
    $score = 100;

    // Title Analysis
    $titleLength = strlen($this->seo['meta_title'] ?: $this->title);
    if ($titleLength < 30) {
        $issues[] = ['type' => 'warning', 'message' => 'Title terlalu pendek (min 30 karakter)'];
        $score -= 10;
    } elseif ($titleLength > 60) {
        $issues[] = ['type' => 'warning', 'message' => 'Title terlalu panjang (max 60 karakter)'];
        $score -= 5;
    }

    // Description Analysis
    $descLength = strlen($this->seo['meta_description'] ?? '');
    if ($descLength === 0) {
        $issues[] = ['type' => 'error', 'message' => 'Meta description kosong'];
        $score -= 20;
    } elseif ($descLength < 120) {
        $issues[] = ['type' => 'warning', 'message' => 'Meta description terlalu pendek (min 120 karakter)'];
        $score -= 10;
    } elseif ($descLength > 160) {
        $issues[] = ['type' => 'warning', 'message' => 'Meta description terlalu panjang (max 160 karakter)'];
        $score -= 5;
    }

    // Focus Keyword Analysis
    $focusKeyword = $this->seo['focus_keyword'] ?? '';
    if ($focusKeyword) {
        if (stripos($this->title, $focusKeyword) === false) {
            $issues[] = ['type' => 'warning', 'message' => 'Focus keyword tidak ada di title'];
            $score -= 10;
        }
        if (stripos($this->seo['meta_description'] ?? '', $focusKeyword) === false) {
            $issues[] = ['type' => 'warning', 'message' => 'Focus keyword tidak ada di meta description'];
            $score -= 5;
        }
    }

    // OG Image
    if (empty($this->seo['og_image']) && empty($this->featured_image)) {
        $issues[] = ['type' => 'info', 'message' => 'Tidak ada OG image (akan menggunakan default)'];
    }

    $this->seoScore = max(0, $score);
    $this->seoAnalysis = $issues;
}
```

#### 6. SEO Panel Blade View

```html
<!-- resources/views/components/admin/seo-panel.blade.php -->

<div x-data="{ open: @entangle('showSeoPanel') }">
    <button type="button" @click="open = !open" class="flex items-center justify-between w-full p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined">search</span>
            <span class="font-medium">SEO Settings</span>
        </div>
        <div class="flex items-center gap-2">
            <!-- SEO Score Badge -->
            <span class="px-2 py-1 text-sm rounded-full
                @if($seoScore >= 80) bg-green-100 text-green-700
                @elseif($seoScore >= 50) bg-yellow-100 text-yellow-700
                @else bg-red-100 text-red-700 @endif">
                {{ $seoScore }}/100
            </span>
            <span class="material-symbols-outlined" x-text="open ? 'expand_less' : 'expand_more'"></span>
        </div>
    </button>

    <div x-show="open" x-collapse class="mt-4 space-y-4">
        <!-- Focus Keyword -->
        <div>
            <label class="block text-sm font-medium mb-1">Focus Keyword</label>
            <input type="text" wire:model.live="seo.focus_keyword"
                   class="w-full rounded-lg border-gray-300"
                   placeholder="Kata kunci utama">
        </div>

        <!-- Meta Title -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Meta Title
                <span class="text-gray-400">({{ strlen($seo['meta_title'] ?: $title) }}/60)</span>
            </label>
            <input type="text" wire:model.live="seo.meta_title"
                   class="w-full rounded-lg border-gray-300"
                   placeholder="{{ $title }}">
            <p class="text-xs text-gray-500 mt-1">Kosongkan untuk menggunakan judul halaman</p>
        </div>

        <!-- Meta Description -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Meta Description
                <span class="text-gray-400">({{ strlen($seo['meta_description'] ?? '') }}/160)</span>
            </label>
            <textarea wire:model.live="seo.meta_description" rows="3"
                      class="w-full rounded-lg border-gray-300"
                      placeholder="Deskripsi singkat halaman untuk search engine"></textarea>
        </div>

        <!-- SEO Analysis -->
        @if(count($seoAnalysis) > 0)
        <div class="p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium mb-2">SEO Analysis</h4>
            <ul class="space-y-1 text-sm">
                @foreach($seoAnalysis as $item)
                <li class="flex items-center gap-2">
                    @if($item['type'] === 'error')
                        <span class="text-red-500">✕</span>
                    @elseif($item['type'] === 'warning')
                        <span class="text-yellow-500">⚠</span>
                    @else
                        <span class="text-blue-500">ℹ</span>
                    @endif
                    {{ $item['message'] }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Advanced SEO -->
        <details class="border rounded-lg">
            <summary class="p-3 cursor-pointer">Advanced SEO</summary>
            <div class="p-4 space-y-4 border-t">
                <!-- Canonical URL -->
                <div>
                    <label class="block text-sm font-medium mb-1">Canonical URL</label>
                    <input type="url" wire:model="seo.canonical_url"
                           class="w-full rounded-lg border-gray-300"
                           placeholder="Kosongkan untuk URL default">
                </div>

                <!-- Robots -->
                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="seo.noindex" class="rounded">
                        <span>No Index</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="seo.nofollow" class="rounded">
                        <span>No Follow</span>
                    </label>
                </div>

                <!-- Sitemap -->
                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="seo.exclude_from_sitemap" class="rounded">
                        <span>Exclude from Sitemap</span>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Sitemap Priority</label>
                        <select wire:model="seo.sitemap_priority" class="w-full rounded-lg border-gray-300">
                            <option value="1.0">1.0 (Highest)</option>
                            <option value="0.8">0.8</option>
                            <option value="0.5">0.5 (Default)</option>
                            <option value="0.3">0.3</option>
                            <option value="0.1">0.1 (Lowest)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Change Frequency</label>
                        <select wire:model="seo.sitemap_changefreq" class="w-full rounded-lg border-gray-300">
                            <option value="always">Always</option>
                            <option value="hourly">Hourly</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="never">Never</option>
                        </select>
                    </div>
                </div>
            </div>
        </details>

        <!-- Social Media -->
        <details class="border rounded-lg">
            <summary class="p-3 cursor-pointer">Social Media / Open Graph</summary>
            <div class="p-4 space-y-4 border-t">
                <!-- OG Title -->
                <div>
                    <label class="block text-sm font-medium mb-1">OG Title</label>
                    <input type="text" wire:model="seo.og_title"
                           class="w-full rounded-lg border-gray-300"
                           placeholder="Kosongkan untuk menggunakan Meta Title">
                </div>

                <!-- OG Description -->
                <div>
                    <label class="block text-sm font-medium mb-1">OG Description</label>
                    <textarea wire:model="seo.og_description" rows="2"
                              class="w-full rounded-lg border-gray-300"
                              placeholder="Kosongkan untuk menggunakan Meta Description"></textarea>
                </div>

                <!-- OG Image -->
                <div>
                    <label class="block text-sm font-medium mb-1">OG Image</label>
                    <livewire:media-picker wire:model="seo.og_image" />
                    <p class="text-xs text-gray-500 mt-1">Recommended: 1200x630 pixels</p>
                </div>
            </div>
        </details>
    </div>
</div>
```

#### 7. SEO Service - SeoService.php

```php
namespace App\Services;

class SeoService
{
    public function generateMetaTags($model): string
    {
        $tags = [];

        // Basic Meta Tags
        $tags[] = '<title>' . e($model->getMetaTitle()) . '</title>';
        $tags[] = '<meta name="description" content="' . e($model->getMetaDescription()) . '">';

        if ($keywords = $model->getMetaKeywords()) {
            $tags[] = '<meta name="keywords" content="' . e($keywords) . '">';
        }

        // Robots
        $tags[] = '<meta name="robots" content="' . $model->getRobots() . '">';

        // Canonical
        $tags[] = '<link rel="canonical" href="' . e($model->getCanonicalUrl()) . '">';

        // Open Graph
        $tags[] = '<meta property="og:type" content="website">';
        $tags[] = '<meta property="og:title" content="' . e($model->getOgTitle()) . '">';
        $tags[] = '<meta property="og:description" content="' . e($model->getOgDescription()) . '">';
        $tags[] = '<meta property="og:url" content="' . e($model->getCanonicalUrl()) . '">';

        if ($ogImage = $model->getOgImage()) {
            $tags[] = '<meta property="og:image" content="' . e($ogImage) . '">';
            $tags[] = '<meta property="og:image:width" content="' . setting('og_image_width', 1200) . '">';
            $tags[] = '<meta property="og:image:height" content="' . setting('og_image_height', 630) . '">';
        }

        // Twitter Card
        $tags[] = '<meta name="twitter:card" content="' . setting('twitter_card_type', 'summary_large_image') . '">';

        if ($twitterSite = setting('twitter_site')) {
            $tags[] = '<meta name="twitter:site" content="' . e($twitterSite) . '">';
        }

        $tags[] = '<meta name="twitter:title" content="' . e($model->getOgTitle()) . '">';
        $tags[] = '<meta name="twitter:description" content="' . e($model->getOgDescription()) . '">';

        if ($ogImage) {
            $tags[] = '<meta name="twitter:image" content="' . e($ogImage) . '">';
        }

        // Hreflang untuk multi-language
        if (method_exists($model, 'getTranslations')) {
            foreach ($model->getTranslations() as $translation) {
                $tags[] = '<link rel="alternate" hreflang="' . $translation->language_code . '" href="' . e($translation->getUrl()) . '">';
            }
        }

        return implode("\n    ", $tags);
    }

    public function generateSchemaScript($model): string
    {
        $schema = $model->toSchemaArray();

        // Add Organization schema
        if (setting('schema_organization_name')) {
            $orgSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => setting('schema_organization_name'),
                'url' => url('/'),
            ];

            if ($logo = setting('schema_organization_logo')) {
                $orgSchema['logo'] = $logo;
            }

            if ($socials = setting('schema_social_profiles')) {
                $orgSchema['sameAs'] = array_column($socials, 'url');
            }

            return '<script type="application/ld+json">' .
                   json_encode([$schema, $orgSchema], JSON_UNESCAPED_SLASHES) .
                   '</script>';
        }

        return '<script type="application/ld+json">' .
               json_encode($schema, JSON_UNESCAPED_SLASHES) .
               '</script>';
    }
}
```

#### 8. Sitemap Generator - SitemapService.php

```php
namespace App\Services;

use App\Models\Page;
use App\Models\CptEntry;
use App\Models\Language;
use Plugins\Posts\Models\Post;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public function generate(): Sitemap
    {
        $sitemap = Sitemap::create();

        if (!setting('sitemap_enabled', true)) {
            return $sitemap;
        }

        $languages = Language::where('is_active', true)->pluck('code');

        // Add Pages
        if (setting('sitemap_include_pages', true)) {
            $this->addPages($sitemap, $languages);
        }

        // Add Posts (dari plugin)
        if (setting('sitemap_include_posts', true) && class_exists(Post::class)) {
            $this->addPosts($sitemap, $languages);
        }

        // Add CPT Entries
        $this->addCptEntries($sitemap, $languages);

        // Add Taxonomies
        if (setting('sitemap_include_taxonomies', true)) {
            $this->addTaxonomies($sitemap, $languages);
        }

        return $sitemap;
    }

    protected function addPages(Sitemap $sitemap, $languages): void
    {
        Page::where('status', 'published')
            ->whereDoesntHave('seo', fn($q) => $q->where('exclude_from_sitemap', true))
            ->each(function ($page) use ($sitemap, $languages) {
                if ($page->shouldExcludeFromSitemap()) return;

                $url = Url::create($page->getUrl())
                    ->setLastModificationDate($page->updated_at)
                    ->setChangeFrequency($page->getSitemapChangefreq())
                    ->setPriority($page->getSitemapPriority());

                // Add alternates untuk multi-language
                foreach ($page->getTranslations() as $translation) {
                    $url->addAlternate($translation->getUrl(), $translation->language_code);
                }

                $sitemap->add($url);
            });
    }

    protected function addPosts(Sitemap $sitemap, $languages): void
    {
        Post::where('status', 'published')
            ->each(function ($post) use ($sitemap) {
                if ($post->shouldExcludeFromSitemap()) return;

                $url = Url::create($post->getUrl())
                    ->setLastModificationDate($post->updated_at)
                    ->setChangeFrequency($post->getSitemapChangefreq())
                    ->setPriority($post->getSitemapPriority());

                foreach ($post->getTranslations() as $translation) {
                    $url->addAlternate($translation->getUrl(), $translation->language_code);
                }

                $sitemap->add($url);
            });
    }

    protected function addCptEntries(Sitemap $sitemap, $languages): void
    {
        CptEntry::where('status', 'published')
            ->with('customPostType')
            ->each(function ($entry) use ($sitemap) {
                if ($entry->shouldExcludeFromSitemap()) return;
                if (!$entry->customPostType->show_in_sitemap) return;

                $url = Url::create($entry->getUrl())
                    ->setLastModificationDate($entry->updated_at)
                    ->setChangeFrequency($entry->getSitemapChangefreq())
                    ->setPriority($entry->getSitemapPriority());

                foreach ($entry->getTranslations() as $translation) {
                    $url->addAlternate($translation->getUrl(), $translation->language_code);
                }

                $sitemap->add($url);
            });
    }

    protected function addTaxonomies(Sitemap $sitemap, $languages): void
    {
        // Categories, Tags, dll
        \App\Models\TaxonomyTerm::with('taxonomy')
            ->whereHas('taxonomy', fn($q) => $q->where('show_in_sitemap', true))
            ->each(function ($term) use ($sitemap) {
                $sitemap->add(
                    Url::create($term->getUrl())
                        ->setChangeFrequency('weekly')
                        ->setPriority(0.6)
                );
            });
    }

    public function generateIndex(): string
    {
        // Untuk sitemap index jika terlalu besar
        return view('sitemap.index', [
            'sitemaps' => [
                ['loc' => url('/sitemap-pages.xml'), 'lastmod' => Page::max('updated_at')],
                ['loc' => url('/sitemap-posts.xml'), 'lastmod' => Post::max('updated_at')],
            ],
        ])->render();
    }
}
```

#### 9. Routes untuk Sitemap & Robots.txt

```php
// routes/web.php

Route::get('/sitemap.xml', function () {
    return response(
        app(SitemapService::class)->generate()->render(),
        200,
        ['Content-Type' => 'application/xml']
    );
})->name('sitemap');

Route::get('/robots.txt', function () {
    $content = setting('robots_txt_content', "User-agent: *\nAllow: /");

    // Append sitemap URL
    if (setting('sitemap_enabled', true)) {
        $content .= "\n\nSitemap: " . route('sitemap');
    }

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');
```

#### 10. Blade Layout dengan SEO

```html
<!-- resources/views/layouts/app.blade.php -->

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" @if($page->language?->is_rtl) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Meta Tags -->
    {!! app(App\Services\SeoService::class)->generateMetaTags($page) !!}

    <!-- Verification Codes -->
    @if($googleVerification = setting('google_site_verification'))
    <meta name="google-site-verification" content="{{ $googleVerification }}">
    @endif

    @if($bingVerification = setting('bing_site_verification'))
    <meta name="msvalidate.01" content="{{ $bingVerification }}">
    @endif

    <!-- Analytics -->
    @if($gaId = setting('google_analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');
    </script>
    @endif

    @if($gtmId = setting('google_tag_manager_id'))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @if($gtmId = setting('google_tag_manager_id'))
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    @yield('content')

    <!-- Schema.org Structured Data -->
    {!! app(App\Services\SeoService::class)->generateSchemaScript($page) !!}
</body>
</html>
```

#### 11. File yang Perlu Dibuat untuk SEO

1. `app/Traits/HasSeo.php`
2. `app/Services/SeoService.php`
3. `app/Services/SitemapService.php`
4. `resources/views/components/admin/seo-panel.blade.php`
5. Update semua model (Page, CptEntry, Post) dengan trait HasSeo
6. Update semua form (PageForm, EntryForm, PostForm) dengan SEO panel

#### 12. Composer Package untuk Sitemap
```bash
composer require spatie/laravel-sitemap
```

---

## 5. Settings > Redirect

**Untuk URL redirect management:**

### General Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `redirects_enabled` | boolean | true | Aktifkan redirect system |
| `log_redirects` | boolean | false | Log redirect hits |
| `log_404` | boolean | true | Log 404 errors |

### Redirect Rules (Table/CRUD)
Ini membutuhkan tabel database terpisah `redirects`:

| Field | Type | Deskripsi |
|-------|------|-----------|
| `id` | bigint | Primary key |
| `from_url` | string | URL asal (path atau full URL) |
| `to_url` | string | URL tujuan |
| `status_code` | enum | 301 (permanent) / 302 (temporary) / 307 / 308 |
| `match_type` | enum | exact / regex / starts_with |
| `is_active` | boolean | Status aktif |
| `hit_count` | integer | Jumlah redirect hits |
| `last_hit_at` | timestamp | Terakhir diakses |
| `notes` | text | Catatan internal |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Auto Redirect Options
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `auto_redirect_on_slug_change` | boolean | true | Auto redirect saat slug berubah |
| `preserve_query_string` | boolean | true | Pertahankan query string |
| `case_insensitive` | boolean | true | Case insensitive matching |

### 404 Handling
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `custom_404_page_id` | select | null | Halaman custom untuk 404 |
| `suggest_similar_pages` | boolean | true | Tampilkan halaman mirip di 404 |

---

### Implementasi Redirect dari Hulu ke Hilir

#### 1. Database Schema

##### Tabel `redirects`
```php
Schema::create('redirects', function (Blueprint $table) {
    $table->id();
    $table->string('from_url', 500);
    $table->string('to_url', 500);
    $table->enum('status_code', ['301', '302', '307', '308'])->default('301');
    $table->enum('match_type', ['exact', 'regex', 'starts_with'])->default('exact');
    $table->boolean('is_active')->default(true);
    $table->unsignedBigInteger('hit_count')->default(0);
    $table->timestamp('last_hit_at')->nullable();
    $table->text('notes')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();

    $table->index(['from_url', 'is_active']);
    $table->index('match_type');
});
```

##### Tabel `redirect_logs` (untuk tracking)
```php
Schema::create('redirect_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('redirect_id')->nullable()->constrained()->nullOnDelete();
    $table->string('from_url', 500);
    $table->string('to_url', 500)->nullable();
    $table->string('type'); // redirect, 404
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->string('referer')->nullable();
    $table->timestamps();

    $table->index(['type', 'created_at']);
    $table->index('from_url');
});
```

#### 2. Model - Redirect.php

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Redirect extends Model
{
    protected $fillable = [
        'from_url', 'to_url', 'status_code', 'match_type',
        'is_active', 'hit_count', 'last_hit_at', 'notes', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hit_count' => 'integer',
        'last_hit_at' => 'datetime',
    ];

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ========== RELATIONSHIPS ==========

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(RedirectLog::class);
    }

    // ========== METHODS ==========

    public function matches(string $requestPath): bool
    {
        $fromUrl = $this->normalizeUrl($this->from_url);
        $requestPath = $this->normalizeUrl($requestPath);

        return match ($this->match_type) {
            'exact' => $this->matchExact($fromUrl, $requestPath),
            'starts_with' => $this->matchStartsWith($fromUrl, $requestPath),
            'regex' => $this->matchRegex($fromUrl, $requestPath),
            default => false,
        };
    }

    protected function matchExact(string $pattern, string $path): bool
    {
        if (setting('redirect_case_insensitive', true)) {
            return strtolower($pattern) === strtolower($path);
        }
        return $pattern === $path;
    }

    protected function matchStartsWith(string $pattern, string $path): bool
    {
        if (setting('redirect_case_insensitive', true)) {
            return Str::startsWith(strtolower($path), strtolower($pattern));
        }
        return Str::startsWith($path, $pattern);
    }

    protected function matchRegex(string $pattern, string $path): bool
    {
        $flags = setting('redirect_case_insensitive', true) ? 'i' : '';
        return (bool) preg_match("#{$pattern}#{$flags}", $path);
    }

    protected function normalizeUrl(string $url): string
    {
        // Remove trailing slashes
        $url = rtrim($url, '/');

        // Ensure starts with /
        if (!Str::startsWith($url, '/') && !Str::startsWith($url, 'http')) {
            $url = '/' . $url;
        }

        return $url;
    }

    public function getDestinationUrl(string $requestPath): string
    {
        $destination = $this->to_url;

        // Handle regex capture groups
        if ($this->match_type === 'regex') {
            $flags = setting('redirect_case_insensitive', true) ? 'i' : '';
            $destination = preg_replace(
                "#{$this->from_url}#{$flags}",
                $this->to_url,
                $requestPath
            );
        }

        // Preserve query string if enabled
        if (setting('redirect_preserve_query_string', true)) {
            $queryString = request()->getQueryString();
            if ($queryString && !Str::contains($destination, '?')) {
                $destination .= '?' . $queryString;
            }
        }

        return $destination;
    }

    public function recordHit(): void
    {
        $this->increment('hit_count');
        $this->update(['last_hit_at' => now()]);

        if (setting('log_redirects', false)) {
            RedirectLog::create([
                'redirect_id' => $this->id,
                'from_url' => request()->path(),
                'to_url' => $this->to_url,
                'type' => 'redirect',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referer' => request()->header('referer'),
            ]);
        }
    }

    // ========== STATIC METHODS ==========

    public static function findMatch(string $path): ?self
    {
        return static::active()
            ->orderByRaw("CASE match_type
                WHEN 'exact' THEN 1
                WHEN 'starts_with' THEN 2
                WHEN 'regex' THEN 3
                END")
            ->get()
            ->first(fn ($redirect) => $redirect->matches($path));
    }
}
```

#### 3. Model - RedirectLog.php

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedirectLog extends Model
{
    protected $fillable = [
        'redirect_id', 'from_url', 'to_url', 'type',
        'ip_address', 'user_agent', 'referer',
    ];

    public function redirect()
    {
        return $this->belongsTo(Redirect::class);
    }

    // ========== SCOPES ==========

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scope404s($query)
    {
        return $query->type('404');
    }

    public function scopeRedirects($query)
    {
        return $query->type('redirect');
    }

    // ========== STATIC METHODS ==========

    public static function log404(string $url): void
    {
        if (!setting('log_404', true)) return;

        static::create([
            'from_url' => $url,
            'type' => '404',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
        ]);
    }
}
```

#### 4. Middleware - HandleRedirects.php

```php
namespace App\Http\Middleware;

use App\Models\Redirect;
use App\Models\RedirectLog;
use Closure;
use Illuminate\Http\Request;

class HandleRedirects
{
    public function handle(Request $request, Closure $next)
    {
        if (!setting('redirects_enabled', true)) {
            return $next($request);
        }

        $path = '/' . ltrim($request->path(), '/');

        // Check for redirect match
        $redirect = Redirect::findMatch($path);

        if ($redirect) {
            $redirect->recordHit();

            $destination = $redirect->getDestinationUrl($path);

            return redirect($destination, (int) $redirect->status_code);
        }

        return $next($request);
    }
}
```

#### 5. Integrasi dengan Pages - Auto Redirect on Slug Change

```php
// app/Models/Page.php

class Page extends Model
{
    // ... existing code

    protected static function booted()
    {
        // Create redirect when slug changes
        static::updating(function ($page) {
            if ($page->isDirty('slug') && setting('auto_redirect_on_slug_change', true)) {
                $oldSlug = $page->getOriginal('slug');
                $newSlug = $page->slug;

                // Don't create if redirect already exists
                if (!Redirect::where('from_url', '/' . $oldSlug)->exists()) {
                    Redirect::create([
                        'from_url' => '/' . $oldSlug,
                        'to_url' => '/' . $newSlug,
                        'status_code' => '301',
                        'match_type' => 'exact',
                        'is_active' => true,
                        'notes' => "Auto-created: Page slug changed from '{$oldSlug}' to '{$newSlug}'",
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        });
    }
}
```

#### 6. Integrasi dengan Posts Plugin

```php
// plugins/posts/src/Models/Post.php

class Post extends Model
{
    protected static function booted()
    {
        static::updating(function ($post) {
            if ($post->isDirty('slug') && setting('auto_redirect_on_slug_change', true)) {
                $archiveSlug = setting('posts.archive_slug', 'blog');
                $oldSlug = $post->getOriginal('slug');
                $newSlug = $post->slug;

                \App\Models\Redirect::create([
                    'from_url' => "/{$archiveSlug}/{$oldSlug}",
                    'to_url' => "/{$archiveSlug}/{$newSlug}",
                    'status_code' => '301',
                    'match_type' => 'exact',
                    'is_active' => true,
                    'notes' => "Auto-created: Post slug changed",
                    'created_by' => auth()->id(),
                ]);
            }
        });
    }
}
```

#### 7. Integrasi dengan CPT Entries

```php
// app/Models/CptEntry.php

class CptEntry extends Model
{
    protected static function booted()
    {
        static::updating(function ($entry) {
            if ($entry->isDirty('slug') && setting('auto_redirect_on_slug_change', true)) {
                $cptSlug = $entry->customPostType->slug;
                $oldSlug = $entry->getOriginal('slug');
                $newSlug = $entry->slug;

                Redirect::create([
                    'from_url' => "/{$cptSlug}/{$oldSlug}",
                    'to_url' => "/{$cptSlug}/{$newSlug}",
                    'status_code' => '301',
                    'match_type' => 'exact',
                    'is_active' => true,
                    'notes' => "Auto-created: {$entry->customPostType->name} slug changed",
                    'created_by' => auth()->id(),
                ]);
            }
        });
    }
}
```

#### 8. Custom 404 Handler

```php
// app/Exceptions/Handler.php

use App\Models\Page;
use App\Models\RedirectLog;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

public function render($request, Throwable $e)
{
    if ($e instanceof NotFoundHttpException) {
        // Log 404
        RedirectLog::log404($request->path());

        // Custom 404 page
        $pageId = setting('custom_404_page_id');
        if ($pageId) {
            $page = Page::find($pageId);
            if ($page) {
                return response()->view('pages.show', [
                    'page' => $page,
                    'suggestions' => $this->getSimilarPages($request->path()),
                ], 404);
            }
        }

        // Default 404 with suggestions
        return response()->view('errors.404', [
            'suggestions' => $this->getSimilarPages($request->path()),
        ], 404);
    }

    return parent::render($request, $e);
}

protected function getSimilarPages(string $path): Collection
{
    if (!setting('suggest_similar_pages', true)) {
        return collect();
    }

    $slug = basename($path);

    return Page::where('status', 'published')
        ->where(function ($query) use ($slug) {
            $query->where('slug', 'like', "%{$slug}%")
                  ->orWhere('title', 'like', "%{$slug}%");
        })
        ->limit(5)
        ->get();
}
```

#### 9. Admin Livewire - RedirectsTable.php

```php
namespace App\Livewire\Admin\Settings;

use App\Models\Redirect;
use App\Models\RedirectLog;
use Livewire\Component;
use Livewire\WithPagination;

class RedirectsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterStatus = '';
    public string $sortBy = 'created_at';
    public string $sortDir = 'desc';

    // Form
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $from_url = '';
    public string $to_url = '';
    public string $status_code = '301';
    public string $match_type = 'exact';
    public bool $is_active = true;
    public string $notes = '';

    protected $rules = [
        'from_url' => 'required|string|max:500',
        'to_url' => 'required|string|max:500',
        'status_code' => 'required|in:301,302,307,308',
        'match_type' => 'required|in:exact,regex,starts_with',
        'is_active' => 'boolean',
        'notes' => 'nullable|string',
    ];

    public function save()
    {
        $this->validate();

        $data = [
            'from_url' => $this->from_url,
            'to_url' => $this->to_url,
            'status_code' => $this->status_code,
            'match_type' => $this->match_type,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ];

        if ($this->editingId) {
            Redirect::find($this->editingId)->update($data);
            session()->flash('message', 'Redirect updated successfully.');
        } else {
            $data['created_by'] = auth()->id();
            Redirect::create($data);
            session()->flash('message', 'Redirect created successfully.');
        }

        $this->resetForm();
    }

    public function edit(int $id)
    {
        $redirect = Redirect::findOrFail($id);

        $this->editingId = $id;
        $this->from_url = $redirect->from_url;
        $this->to_url = $redirect->to_url;
        $this->status_code = $redirect->status_code;
        $this->match_type = $redirect->match_type;
        $this->is_active = $redirect->is_active;
        $this->notes = $redirect->notes ?? '';
        $this->showForm = true;
    }

    public function delete(int $id)
    {
        Redirect::findOrFail($id)->delete();
        session()->flash('message', 'Redirect deleted successfully.');
    }

    public function toggleStatus(int $id)
    {
        $redirect = Redirect::findOrFail($id);
        $redirect->update(['is_active' => !$redirect->is_active]);
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'from_url', 'to_url', 'status_code', 'match_type', 'is_active', 'notes']);
        $this->showForm = false;
    }

    public function getRedirectsProperty()
    {
        return Redirect::query()
            ->when($this->search, fn($q) => $q->where('from_url', 'like', "%{$this->search}%")
                                              ->orWhere('to_url', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn($q) => $q->where('match_type', $this->filterType))
            ->when($this->filterStatus !== '', fn($q) => $q->where('is_active', $this->filterStatus === 'active'))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(20);
    }

    public function getStatsProperty()
    {
        return [
            'total' => Redirect::count(),
            'active' => Redirect::active()->count(),
            'total_hits' => Redirect::sum('hit_count'),
            '404_today' => RedirectLog::type('404')->whereDate('created_at', today())->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.settings.redirects-table', [
            'redirects' => $this->redirects,
            'stats' => $this->stats,
        ]);
    }
}
```

#### 10. 404 Logs View - untuk melihat 404 dan convert ke redirect

```php
namespace App\Livewire\Admin\Settings;

use App\Models\Redirect;
use App\Models\RedirectLog;
use Livewire\Component;
use Livewire\WithPagination;

class NotFoundLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public function createRedirect(int $logId)
    {
        $log = RedirectLog::findOrFail($logId);

        // Redirect ke form dengan from_url pre-filled
        return redirect()->route('admin.settings.redirects', [
            'from_url' => $log->from_url,
        ]);
    }

    public function clearLogs()
    {
        RedirectLog::type('404')->delete();
        session()->flash('message', '404 logs cleared.');
    }

    public function render()
    {
        $logs = RedirectLog::type('404')
            ->when($this->search, fn($q) => $q->where('from_url', 'like', "%{$this->search}%"))
            ->select('from_url')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('MAX(created_at) as last_hit')
            ->groupBy('from_url')
            ->orderByDesc('count')
            ->paginate(50);

        return view('livewire.admin.settings.not-found-logs', [
            'logs' => $logs,
        ]);
    }
}
```

#### 11. Register Middleware

```php
// bootstrap/app.php atau app/Http/Kernel.php

// Add to web middleware group (sebelum route handling)
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(prepend: [
        \App\Http\Middleware\HandleRedirects::class,
    ]);
})
```

#### 12. Routes untuk Redirect Management

```php
// routes/admin.php

Route::prefix('settings/redirects')->name('admin.settings.redirects.')->group(function () {
    Route::get('/', [RedirectController::class, 'index'])->name('index');
    Route::get('/404-logs', [RedirectController::class, 'notFoundLogs'])->name('404-logs');
    Route::post('/import', [RedirectController::class, 'import'])->name('import');
    Route::get('/export', [RedirectController::class, 'export'])->name('export');
});
```

#### 13. Import/Export Feature

```php
// RedirectController.php

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt',
    ]);

    $file = $request->file('file');
    $rows = array_map('str_getcsv', file($file->getPathname()));
    $header = array_shift($rows);

    $imported = 0;
    foreach ($rows as $row) {
        $data = array_combine($header, $row);

        Redirect::updateOrCreate(
            ['from_url' => $data['from_url']],
            [
                'to_url' => $data['to_url'],
                'status_code' => $data['status_code'] ?? '301',
                'match_type' => $data['match_type'] ?? 'exact',
                'is_active' => ($data['is_active'] ?? 'true') === 'true',
                'notes' => $data['notes'] ?? 'Imported',
                'created_by' => auth()->id(),
            ]
        );
        $imported++;
    }

    return back()->with('message', "Imported {$imported} redirects.");
}

public function export()
{
    $redirects = Redirect::all();

    $csv = "from_url,to_url,status_code,match_type,is_active,hit_count,notes\n";

    foreach ($redirects as $redirect) {
        $csv .= "\"{$redirect->from_url}\",\"{$redirect->to_url}\",{$redirect->status_code},{$redirect->match_type}," .
                ($redirect->is_active ? 'true' : 'false') . ",{$redirect->hit_count},\"{$redirect->notes}\"\n";
    }

    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="redirects-' . date('Y-m-d') . '.csv"');
}
```

#### 14. File yang Perlu Dibuat untuk Redirect

1. `app/Models/Redirect.php`
2. `app/Models/RedirectLog.php`
3. `app/Http/Middleware/HandleRedirects.php`
4. `app/Livewire/Admin/Settings/RedirectsTable.php`
5. `app/Livewire/Admin/Settings/NotFoundLogs.php`
6. `app/Http/Controllers/Admin/RedirectController.php`
7. `database/migrations/create_redirects_table.php`
8. `database/migrations/create_redirect_logs_table.php`
9. `resources/views/livewire/admin/settings/redirects-table.blade.php`
10. `resources/views/livewire/admin/settings/not-found-logs.blade.php`
11. `resources/views/errors/404.blade.php` (dengan suggestions)

---

## Struktur Database yang Direkomendasikan

### Tabel `settings` (Core CMS)
```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('group')->default('general'); // general, brevo, languages, seo, redirect
    $table->string('type')->default('string'); // string, boolean, number, json, etc.
    $table->timestamps();
});
```

### Tabel `redirects`
```php
Schema::create('redirects', function (Blueprint $table) {
    $table->id();
    $table->string('from_url');
    $table->string('to_url');
    $table->enum('status_code', [301, 302, 307, 308])->default(301);
    $table->enum('match_type', ['exact', 'regex', 'starts_with'])->default('exact');
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('hit_count')->default(0);
    $table->timestamp('last_hit_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['from_url', 'is_active']);
});
```

---

## Implementasi yang Diperlukan

### File yang Perlu Dibuat:
1. `app/Models/Setting.php` - Model untuk settings (core)
2. `app/Models/Redirect.php` - Model untuk redirects
3. `app/Livewire/Admin/Settings/GeneralSettings.php`
4. `app/Livewire/Admin/Settings/BrevoSettings.php`
5. `app/Livewire/Admin/Settings/LanguageSettings.php`
6. `app/Livewire/Admin/Settings/SeoSettings.php`
7. `app/Livewire/Admin/Settings/RedirectSettings.php`
8. `app/Http/Controllers/Admin/SettingsController.php`
9. `app/Http/Middleware/HandleRedirects.php`
10. `app/Services/BrevoService.php` (jika integrasi Brevo)
11. Views untuk masing-masing settings page
12. Migrations untuk tabel `settings` dan `redirects`

### Routes yang Perlu Ditambahkan:
```php
Route::prefix('settings')->name('admin.settings.')->group(function () {
    Route::get('/general', [SettingsController::class, 'general'])->name('general');
    Route::get('/brevo', [SettingsController::class, 'brevo'])->name('brevo');
    Route::get('/languages', [SettingsController::class, 'languages'])->name('languages');
    Route::get('/seo', [SettingsController::class, 'seo'])->name('seo');
    Route::get('/redirects', [SettingsController::class, 'redirects'])->name('redirects');
});
```

---

---

## 6. LiteSpeed Cache > Cache

**Untuk konfigurasi caching utama:**

### Cache Control
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `lscache_enabled` | boolean | true | Aktifkan LiteSpeed Cache |
| `cache_logged_in_users` | boolean | false | Cache untuk logged-in users |
| `cache_commenters` | boolean | false | Cache untuk users yang sudah komentar |
| `cache_rest_api` | boolean | true | Cache REST API responses |
| `cache_login_page` | boolean | false | Cache halaman login |

### TTL (Time To Live)
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `ttl_public` | number | 604800 | TTL untuk public cache (detik) - default 7 hari |
| `ttl_private` | number | 1800 | TTL untuk private cache (detik) - default 30 menit |
| `ttl_frontpage` | number | 604800 | TTL untuk homepage |
| `ttl_feed` | number | 604800 | TTL untuk RSS feed |
| `ttl_rest_api` | number | 604800 | TTL untuk REST API |
| `ttl_404` | number | 3600 | TTL untuk 404 pages (1 jam) |

### Purge Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `purge_on_upgrade` | boolean | true | Purge cache saat CMS upgrade |
| `purge_stale` | boolean | true | Serve stale content saat regenerating |
| `auto_purge_rules` | multi-select | ['post_update', 'comment_add'] | Kapan auto purge |

### Cache Exclusions
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `cache_exclude_urls` | textarea | '' | URL patterns yang tidak di-cache (per baris) |
| `cache_exclude_query_strings` | textarea | '' | Query strings yang tidak di-cache |
| `cache_exclude_cookies` | textarea | '' | Cookies yang disable cache |
| `cache_exclude_user_agents` | textarea | '' | User agents yang tidak di-cache |

### Cache Actions (Buttons)
| Action | Deskripsi |
|--------|-----------|
| `Purge All` | Hapus semua cache |
| `Purge Front Page` | Hapus cache homepage |
| `Purge Pages` | Hapus cache semua pages |
| `Purge Posts` | Hapus cache semua posts |
| `Purge CSS/JS` | Hapus cache CSS dan JS |

---

## 7. LiteSpeed Cache > CDN

**Untuk Content Delivery Network:**

### CDN Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `cdn_enabled` | boolean | false | Aktifkan CDN |
| `cdn_url` | string | '' | CDN URL (e.g., cdn.example.com) |
| `cdn_include_images` | boolean | true | Serve images via CDN |
| `cdn_include_css` | boolean | true | Serve CSS via CDN |
| `cdn_include_js` | boolean | true | Serve JavaScript via CDN |

### QUIC.cloud CDN (LiteSpeed's CDN)
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `quic_cloud_enabled` | boolean | false | Gunakan QUIC.cloud |
| `quic_cloud_api_key` | password | '' | QUIC.cloud API key |
| `quic_cloud_email` | email | '' | Email akun QUIC.cloud |

### CDN Mapping
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `cdn_original_urls` | textarea | '' | Original URLs (per baris) |
| `cdn_mapped_urls` | textarea | '' | CDN URLs mapping (per baris) |
| `cdn_include_dirs` | textarea | 'uploads\nthemes\nplugins' | Directories untuk CDN |

### Cloudflare Integration
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `cloudflare_enabled` | boolean | false | Aktifkan Cloudflare API |
| `cloudflare_api_token` | password | '' | Cloudflare API token |
| `cloudflare_zone_id` | string | '' | Zone ID dari Cloudflare |

---

## 8. LiteSpeed Cache > Image Optimization

**Untuk optimasi gambar:**

### Image Optimization Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `img_optm_enabled` | boolean | false | Aktifkan image optimization |
| `img_optm_auto` | boolean | true | Auto optimize saat upload |
| `img_optm_cron` | boolean | false | Optimize via cron job |

### Optimization Level
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `img_optm_level` | select | 'lossy' | Level kompresi (lossless/lossy/ultra) |
| `img_optm_quality` | number | 82 | Kualitas JPEG (1-100) |
| `img_optm_webp` | boolean | true | Generate WebP format |
| `img_optm_webp_replace` | boolean | true | Replace original dengan WebP |
| `img_optm_avif` | boolean | false | Generate AVIF format |

### Lazy Load
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `lazyload_images` | boolean | true | Lazy load gambar |
| `lazyload_iframes` | boolean | true | Lazy load iframes (YouTube, etc.) |
| `lazyload_placeholder` | select | 'spinner' | Placeholder type (spinner/grey/none) |
| `lazyload_exclude_classes` | text | '' | CSS classes yang dikecualikan |

### Responsive Images
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `responsive_images` | boolean | true | Generate responsive sizes |
| `responsive_sizes` | text | '200,400,600,800,1200' | Ukuran yang di-generate (px) |

### Image Actions (Buttons)
| Action | Deskripsi |
|--------|-----------|
| `Optimize All` | Optimize semua gambar existing |
| `Clean Up` | Hapus file optimized yang tidak terpakai |
| `Calculate Savings` | Hitung space yang dihemat |

---

## 9. LiteSpeed Cache > Page Optimization

**Untuk optimasi halaman dan assets:**

### CSS Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `css_minify` | boolean | true | Minify CSS |
| `css_combine` | boolean | false | Combine CSS files |
| `css_http2_push` | boolean | false | HTTP/2 push untuk CSS |
| `css_async_load` | boolean | false | Load CSS secara async |
| `css_exclude` | textarea | '' | CSS files yang dikecualikan |

### Critical CSS (UCSS)
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `ucss_enabled` | boolean | false | Generate Critical CSS |
| `ucss_inline` | boolean | true | Inline critical CSS |
| `ucss_async_full` | boolean | true | Load full CSS async |

### JavaScript Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `js_minify` | boolean | true | Minify JavaScript |
| `js_combine` | boolean | false | Combine JS files |
| `js_defer` | boolean | true | Defer non-critical JS |
| `js_delay` | boolean | false | Delay JS execution |
| `js_delay_timeout` | number | 5000 | Delay timeout (ms) |
| `js_exclude` | textarea | '' | JS files yang dikecualikan |

### HTML Settings
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `html_minify` | boolean | true | Minify HTML |
| `html_remove_comments` | boolean | true | Hapus HTML comments |
| `html_remove_query_strings` | boolean | true | Hapus query strings dari static resources |

### DNS Prefetch
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `dns_prefetch_enabled` | boolean | true | Aktifkan DNS prefetch |
| `dns_prefetch_domains` | textarea | '' | Domains untuk prefetch (per baris) |

### Preload
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `preload_css` | boolean | false | Preload critical CSS |
| `preload_js` | boolean | false | Preload critical JS |
| `preload_fonts` | textarea | '' | Font URLs untuk preload |

### Localization
| Setting | Type | Default | Deskripsi |
|---------|------|---------|-----------|
| `localize_gravatar` | boolean | false | Host Gravatar lokal |
| `localize_google_fonts` | boolean | false | Host Google Fonts lokal |

---

## Struktur Database untuk LiteSpeed Cache

### Update Tabel `settings` - Tambah Group
```php
// Groups yang perlu ditambahkan:
// - lscache (untuk Cache settings)
// - cdn (untuk CDN settings)
// - image_optm (untuk Image Optimization)
// - page_optm (untuk Page Optimization)
```

### Tabel `lscache_logs` (Opsional - untuk tracking)
```php
Schema::create('lscache_logs', function (Blueprint $table) {
    $table->id();
    $table->string('action'); // purge, optimize, etc.
    $table->string('target')->nullable(); // URL atau file yang di-purge/optimize
    $table->text('details')->nullable();
    $table->foreignId('user_id')->nullable()->constrained();
    $table->timestamps();
});
```

---

## File Implementasi untuk LiteSpeed Cache

### File yang Perlu Dibuat:
1. `app/Livewire/Admin/LiteSpeed/CacheSettings.php`
2. `app/Livewire/Admin/LiteSpeed/CdnSettings.php`
3. `app/Livewire/Admin/LiteSpeed/ImageOptimization.php`
4. `app/Livewire/Admin/LiteSpeed/PageOptimization.php`
5. `app/Http/Controllers/Admin/LiteSpeedController.php`
6. `app/Services/LiteSpeedCacheService.php` - Service untuk manage cache
7. `app/Services/ImageOptimizationService.php` - Service untuk optimize gambar
8. Views untuk masing-masing settings page
9. `app/Http/Middleware/LiteSpeedCache.php` - Middleware untuk set cache headers

### Routes yang Perlu Ditambahkan:
```php
Route::prefix('litespeed')->name('admin.litespeed.')->group(function () {
    Route::get('/cache', [LiteSpeedController::class, 'cache'])->name('cache');
    Route::get('/cdn', [LiteSpeedController::class, 'cdn'])->name('cdn');
    Route::get('/image-optimization', [LiteSpeedController::class, 'imageOptimization'])->name('image-optimization');
    Route::get('/page-optimization', [LiteSpeedController::class, 'pageOptimization'])->name('page-optimization');

    // Cache Actions
    Route::post('/purge-all', [LiteSpeedController::class, 'purgeAll'])->name('purge-all');
    Route::post('/purge-url', [LiteSpeedController::class, 'purgeUrl'])->name('purge-url');

    // Image Actions
    Route::post('/optimize-images', [LiteSpeedController::class, 'optimizeImages'])->name('optimize-images');
});
```

---

## Verifikasi

Setelah implementasi, verifikasi dengan:
1. Akses setiap halaman settings dan pastikan form tampil dengan benar
2. Test save/load untuk setiap setting
3. Test Brevo API connection (jika ada API key)
4. Test redirect rules berfungsi
5. Cek sitemap.xml dan robots.txt di-generate dengan benar
6. Test language switching jika multi-language diaktifkan
7. Test LiteSpeed Cache purge functionality
8. Test image optimization dengan upload gambar baru
9. Cek response headers untuk memastikan cache headers terkirim dengan benar
10. Test CSS/JS minification dan lazy loading
