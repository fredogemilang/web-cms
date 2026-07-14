<?php

namespace App\Traits;

/**
 * Per-model translation helper.
 *
 * Models opt in by:
 *   use HasTranslations;
 *   protected array $translatable = ['title', 'slug', 'content'];
 *   protected $casts = ['translations' => 'array'];
 *
 * Storage shape (in the `translations` JSON column):
 *   { "en": { "title": "About", "slug": "about" }, "ja": { ... } }
 *
 * Default locale (config('app.locale') or setting('default_locale'))
 * is stored in the primary columns (title, slug, ...) — NOT in the JSON.
 * This keeps existing queries (where slug = ?) working unchanged.
 */
trait HasTranslations
{
    public function getTranslation(string $field, ?string $locale = null, bool $fallback = true): mixed
    {
        $locale ??= app()->getLocale();

        if ($this->isDefaultLocale($locale)) {
            return $this->getAttribute($field);
        }

        $translations = $this->translations ?? [];
        $value = $translations[$locale][$field] ?? null;

        if ($value === null && $fallback) {
            return $this->getAttribute($field);
        }

        return $value;
    }

    public function setTranslation(string $field, string $locale, mixed $value): self
    {
        $this->ensureFieldIsTranslatable($field);

        if ($this->isDefaultLocale($locale)) {
            $this->setAttribute($field, $value);

            return $this;
        }

        $translations = $this->translations ?? [];
        $translations[$locale][$field] = $value;
        $this->translations = $this->pruneEmpty($translations);

        return $this;
    }

    public function forgetTranslation(string $field, string $locale): self
    {
        if ($this->isDefaultLocale($locale)) {
            // Don't allow clearing the default — that would wipe the column
            return $this;
        }

        $translations = $this->translations ?? [];
        unset($translations[$locale][$field]);
        if (empty($translations[$locale])) {
            unset($translations[$locale]);
        }
        $this->translations = $translations ?: null;

        return $this;
    }

    public function translate(string $field): mixed
    {
        return $this->getTranslation($field);
    }

    public function hasTranslation(string $field, string $locale): bool
    {
        if ($this->isDefaultLocale($locale)) {
            return $this->getAttribute($field) !== null;
        }

        return isset($this->translations[$locale][$field]) && $this->translations[$locale][$field] !== '';
    }

    /** Get all locales that have at least one translated field on this row. */
    public function translatedLocales(): array
    {
        $locales = [static::defaultLocale()];
        foreach (array_keys($this->translations ?? []) as $l) {
            if (! in_array($l, $locales, true)) {
                $locales[] = $l;
            }
        }

        return $locales;
    }

    /** All translatable field names for this model. */
    public function translatableFields(): array
    {
        return $this->translatable ?? [];
    }

    public static function defaultLocale(): string
    {
        return setting('default_locale', config('app.locale', 'en'));
    }

    protected function isDefaultLocale(string $locale): bool
    {
        return $locale === static::defaultLocale();
    }

    protected function ensureFieldIsTranslatable(string $field): void
    {
        $allowed = $this->translatable ?? [];
        if (! in_array($field, $allowed, true)) {
            throw new \InvalidArgumentException(
                "Field [{$field}] is not translatable on ".static::class.'. Add it to $translatable.'
            );
        }
    }

    protected function pruneEmpty(array $translations): ?array
    {
        $out = [];
        foreach ($translations as $locale => $fields) {
            $clean = array_filter($fields, fn ($v) => $v !== null && $v !== '');
            if (! empty($clean)) {
                $out[$locale] = $clean;
            }
        }

        return $out ?: null;
    }
}
