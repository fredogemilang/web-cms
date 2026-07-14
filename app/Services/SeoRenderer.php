<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class SeoRenderer
{
    public function __construct(protected SchemaBuilder $schemaBuilder) {}

    /**
     * Resolve final SEO data for an entity (merged with site defaults).
     *
     * @return array{title:string,description:?string,canonical:?string,robots:string,og:array,twitter:array,schema:?array}
     */
    public function resolve(?Model $entity, array $overrides = []): array
    {
        $meta = $entity && method_exists($entity, 'seoMeta') ? $entity->seoMeta : null;

        $siteName = setting('site_name', config('app.name'));
        $tagline = setting('site_tagline', '');
        $titleTemplate = setting('seo_title_pattern', '{page} | {site}');

        $rawTitle = $overrides['title']
            ?? $meta?->title
            ?? ($entity->title ?? $siteName);

        $title = strtr($titleTemplate, [
            '{page}' => $rawTitle,
            '{site}' => $siteName,
            '{tagline}' => $tagline,
        ]);

        $description = $overrides['description']
            ?? $meta?->description
            ?? setting('seo_default_description');

        $canonical = $overrides['canonical']
            ?? $meta?->canonical_url
            ?? (method_exists($entity, 'getUrl') ? $entity->getUrl() : request()->fullUrl());

        $robots = $meta?->robots ?? 'index,follow';
        if (! setting('seo_allow_indexing', true)) {
            $robots = 'noindex,nofollow';
        }

        $ogImage = $meta?->ogImage?->path
            ?? setting('seo_default_og_image');

        $og = [
            'title' => $meta?->og_title ?: $title,
            'description' => $meta?->og_description ?: $description,
            'image' => $ogImage ? url($ogImage) : null,
            'type' => $this->ogType($entity),
            'url' => $canonical,
            'site_name' => $siteName,
        ];

        $twitter = [
            'card' => $meta?->twitter_card ?? 'summary_large_image',
            'title' => $og['title'],
            'description' => $og['description'],
            'image' => $og['image'],
        ];

        $schema = $entity ? $this->schemaBuilder->build($entity, $meta) : null;

        return compact('title', 'description', 'canonical', 'robots', 'og', 'twitter', 'schema');
    }

    protected function ogType(?Model $entity): string
    {
        if (! $entity) {
            return 'website';
        }
        $class = class_basename($entity);

        return match ($class) {
            'Post' => 'article',
            'Event' => 'event',
            default => 'website',
        };
    }
}
