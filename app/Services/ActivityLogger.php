<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;

/**
 * Central audit log writer. Resolves user / IP / UA from the request context
 * so callers only need to describe the event.
 *
 * Usage:
 *   app(ActivityLogger::class)->log('page.created', $page, "Created page '{$page->title}'");
 *   app(ActivityLogger::class)->log('user.login', $user, "Signed in");
 *   app(ActivityLogger::class)->log('setting.updated', null, 'site_name changed', ['old' => 'X', 'new' => 'Y']);
 *
 * Or via global helper:
 *   activity()->log(...)
 */
class ActivityLogger
{
    /** Field names whose values must be redacted from properties. */
    protected const SENSITIVE_KEYS = ['password', 'password_confirmation', 'remember_token', 'api_token', 'api_key'];

    public function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): Activity
    {
        return Activity::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => static::redactSensitive($properties) ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }

    /**
     * Convenience: log an Eloquent model update diff. Only includes attributes
     * that actually changed; masks sensitive values.
     */
    public function logModelChanges(string $action, Model $model, ?string $description = null): ?Activity
    {
        $changes = $model->getChanges();
        if (empty($changes)) {
            return null;
        }

        $original = [];
        foreach (array_keys($changes) as $key) {
            $original[$key] = $model->getOriginal($key);
        }

        return $this->log($action, $model, $description, [
            'old' => static::redactSensitive($original),
            'new' => static::redactSensitive($changes),
        ]);
    }

    public static function redactSensitive(array $values): array
    {
        foreach ($values as $key => $value) {
            if (in_array($key, static::SENSITIVE_KEYS, true)) {
                $values[$key] = '••• redacted •••';
            } elseif (is_array($value)) {
                $values[$key] = static::redactSensitive($value);
            }
        }

        return $values;
    }
}
