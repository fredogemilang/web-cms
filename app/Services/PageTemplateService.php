<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageBlock;

class PageTemplateService
{
    public function __construct(protected ThemeLoader $themeLoader) {}

    /**
     * Get block preset definitions for a template from the active theme.
     * Returns array of ['name', 'type', 'label', 'default', 'options'].
     */
    public function getTemplateSchema(string $templateName): array
    {
        $theme = $this->themeLoader->getActiveTheme();

        return $theme ? $theme->getTemplateBlockSchema($templateName) : [];
    }

    /**
     * Seed blocks from template preset onto a page.
     * Only creates blocks that don't already exist on the page (matched by name).
     * Returns names of newly created blocks.
     */
    public function seedBlocks(Page $page): array
    {
        $seeded = [];
        $schema = $this->getTemplateSchema($page->template);
        $existingNames = $page->allBlocks()->pluck('name')->toArray();

        foreach ($schema as $order => $blockDef) {
            if (in_array($blockDef['name'], $existingNames)) {
                continue; // already exists, skip
            }

            PageBlock::create([
                'page_id' => $page->id,
                'name' => $blockDef['name'],
                'type' => $blockDef['type'],
                'label' => $blockDef['label'] ?? ucfirst(str_replace('_', ' ', $blockDef['name'])),
                'value' => $blockDef['default'] ?? $this->defaultForType($blockDef['type']),
                'options' => $blockDef['options'] ?? [],
                'order' => $order,
                'is_active' => true,
            ]);

            $seeded[] = $blockDef['name'];
        }

        return $seeded;
    }

    /**
     * Get default value for a block type.
     */
    protected function defaultForType(string $type): mixed
    {
        return match ($type) {
            'switcher' => false,
            'number' => 0,
            'checkbox', 'gallery', 'posts', 'repeater' => '[]',
            default => '',
        };
    }
}
