<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\View;

class TemplateResolver
{
    protected ThemeLoader $themeLoader;

    public function __construct(ThemeLoader $themeLoader)
    {
        $this->themeLoader = $themeLoader;
    }

    /**
     * Resolve the template to use based on hierarchy.
     *
     * @param string $type Template type (single, archive, page, home, etc.)
     * @param array $context Additional context (post_type, slug, etc.)
     * @return string View name to render
     */
    public function resolve(string $type, array $context = []): string
    {
        $theme = $this->themeLoader->getActiveTheme();

        if (!$theme) {
            return $this->getCoreTemplate($type);
        }

        $hierarchy = $this->buildHierarchy($type, $context);

        foreach ($hierarchy as $template) {
            // 1. Check active theme
            $themeView = "themes::{$theme->slug}.{$template}";
            if (View::exists($themeView)) {
                return $themeView;
            }

            // 2. Check plugin override (if plugin context provided)
            if (isset($context['plugin'])) {
                $pluginView = "{$context['plugin']}::{$template}";
                if (View::exists($pluginView)) {
                    return $pluginView;
                }
            }
        }

        // 3. Core fallback
        return $this->getCoreTemplate($type);
    }

    /**
     * Build template hierarchy based on type and context.
     *
     * @param string $type
     * @param array $context
     * @return array List of template names in priority order
     */
    protected function buildHierarchy(string $type, array $context): array
    {
        return match($type) {
            'single' => $this->buildSingleHierarchy($context),
            'archive' => $this->buildArchiveHierarchy($context),
            'taxonomy' => $this->buildTaxonomyHierarchy($context),
            'page' => $this->buildPageHierarchy($context),
            'home' => ['layouts.home', 'layouts.index'],
            'search' => ['layouts.search', 'layouts.archive'],
            '404' => ['layouts.404'],
            default => ["layouts.{$type}"]
        };
    }

    /**
     * Build hierarchy for single post/entry templates.
     *
     * Priority:
     * - plugins.{plugin}.single-{post_type}-{slug}
     * - plugins.{plugin}.single-{post_type}
     * - plugins.{plugin}.show
     * - layouts.single-{post_type}
     * - layouts.single
     */
    protected function buildSingleHierarchy(array $context): array
    {
        $hierarchy = [];
        $plugin = $context['plugin'] ?? null;
        $postType = $context['post_type'] ?? null;
        $slug = $context['slug'] ?? null;

        if ($plugin && $postType && $slug) {
            $hierarchy[] = "plugins.{$plugin}.single-{$postType}-{$slug}";
        }

        if ($plugin && $postType) {
            $hierarchy[] = "plugins.{$plugin}.single-{$postType}";
        }

        if ($plugin) {
            $hierarchy[] = "plugins.{$plugin}.show";
        }

        if ($postType) {
            $hierarchy[] = "layouts.single-{$postType}";
        }

        $hierarchy[] = 'layouts.single';

        return $hierarchy;
    }

    /**
     * Build hierarchy for archive/list templates.
     *
     * Priority:
     * - plugins.{plugin}.archive-{post_type}
     * - plugins.{plugin}.index
     * - layouts.archive-{post_type}
     * - layouts.archive
     */
    protected function buildArchiveHierarchy(array $context): array
    {
        $hierarchy = [];
        $plugin = $context['plugin'] ?? null;
        $postType = $context['post_type'] ?? null;

        if ($plugin && $postType) {
            $hierarchy[] = "plugins.{$plugin}.archive-{$postType}";
        }

        if ($plugin) {
            $hierarchy[] = "plugins.{$plugin}.index";
        }

        if ($postType) {
            $hierarchy[] = "layouts.archive-{$postType}";
        }

        $hierarchy[] = 'layouts.archive';

        return $hierarchy;
    }

    /**
     * Build hierarchy for taxonomy templates.
     *
     * Priority:
     * - plugins.{plugin}.taxonomy-{taxonomy}-{term}
     * - plugins.{plugin}.taxonomy-{taxonomy}
     * - plugins.{plugin}.taxonomy
     * - layouts.taxonomy-{taxonomy}
     * - layouts.taxonomy
     * - layouts.archive
     */
    protected function buildTaxonomyHierarchy(array $context): array
    {
        $hierarchy = [];
        $plugin = $context['plugin'] ?? null;
        $taxonomy = $context['taxonomy'] ?? null;
        $term = $context['term'] ?? null;

        if ($plugin && $taxonomy && $term) {
            $hierarchy[] = "plugins.{$plugin}.taxonomy-{$taxonomy}-{$term}";
        }

        if ($plugin && $taxonomy) {
            $hierarchy[] = "plugins.{$plugin}.taxonomy-{$taxonomy}";
        }

        if ($plugin) {
            $hierarchy[] = "plugins.{$plugin}.taxonomy";
        }

        if ($taxonomy) {
            $hierarchy[] = "layouts.taxonomy-{$taxonomy}";
        }

        $hierarchy[] = 'layouts.taxonomy';
        $hierarchy[] = 'layouts.archive';

        return $hierarchy;
    }

    /**
     * Build hierarchy for page templates.
     *
     * Priority:
     * - layouts.page-{slug}
     * - layouts.page-{template}
     * - layouts.page
     */
    protected function buildPageHierarchy(array $context): array
    {
        $hierarchy = [];
        $slug = $context['slug'] ?? null;
        $template = $context['template'] ?? null;

        if ($slug) {
            $hierarchy[] = "layouts.page-{$slug}";
        }

        if ($template) {
            $hierarchy[] = "layouts.page-{$template}";
        }

        $hierarchy[] = 'layouts.page';

        return $hierarchy;
    }

    /**
     * Get core template fallback.
     */
    protected function getCoreTemplate(string $type): string
    {
        return match($type) {
            'single' => 'layouts.single',
            'archive' => 'layouts.archive',
            'page' => 'layouts.page',
            'home' => 'layouts.home',
            'search' => 'layouts.search',
            '404' => 'layouts.404',
            default => "layouts.{$type}"
        };
    }
}
