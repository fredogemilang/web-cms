<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageRevision;
use App\Services\PageTemplateService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class PageForm extends Component
{
    protected PageTemplateService $templateService;

    public ?Page $page = null;

    public ?int $pageId = null;

    public bool $isEdit = false;

    // Page fields
    public string $title = '';

    public string $slug = '';

    public ?int $parentId = null;

    public int $menuOrder = 0;

    public string $status = 'draft';

    public ?string $publishedAt = null;

    public string $template = 'default';

    public ?string $featuredImage = null;

    // SEO fields
    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $ogTitle = '';

    public string $ogDescription = '';

    public ?string $ogImage = null;

    // Blocks (array of block data)
    public array $blocks = [];

    // UI State
    public bool $showBlockSelector = false;

    public bool $showMediaPicker = false;

    public ?string $mediaPickerField = null;

    public ?int $editingBlockIndex = null;

    public bool $showSeoSettings = false;

    // Autosave
    public bool $hasUnsavedChanges = false;

    public ?string $lastSavedAt = null;

    // Slug generation
    public bool $manualSlug = false;

    // === Translations state ===
    /** Locale currently shown in the form. */
    public string $editingLocale = '';

    /** Snapshots of translatable fields per non-current locale. Default locale's data lives in the regular form props. */
    public array $localizedSnapshots = [];

    /**
     * Per-locale block value snapshots: { locale => [ blockIndex => value, ... ] }
     * For repeaters: value is itself an array { childIndex => childValue, ... }.
     * Default locale's values stay in $blocks; we mirror them here on locale switches for round-trip.
     */
    public array $localizedBlockValues = [];

    /** Cached list of available locales from Settings — populated in mount(). */
    public array $availableLocales = [];

    /** Translatable form fields (the form keys, not the DB columns). */
    protected array $translatableFormFields = [
        'title', 'slug', 'metaTitle', 'metaDescription', 'ogTitle', 'ogDescription', 'ogImage',
    ];

    protected function rules(): array
    {
        $isDefaultLocale = $this->editingLocale === Page::defaultLocale();

        return [
            // Title required on default locale; optional on translations (fallback to default).
            'title' => $isDefaultLocale ? 'required|string|max:255' : 'nullable|string|max:255',
            'slug' => $isDefaultLocale
                ? ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('pages', 'slug')->ignore($this->pageId)]
                : ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'status' => 'required|in:draft,published,scheduled,private',
            'parentId' => 'nullable|exists:pages,id',
            'template' => 'required|string',
            'publishedAt' => 'nullable|date',
            'blocks.*.name' => 'required|string|max:255|regex:/^[a-z][a-z0-9_]*$/',
            'blocks.*.type' => 'required|in:'.implode(',', array_keys(PageBlock::$blockTypes)),
        ];
    }

    protected $messages = [
        'slug.regex' => 'Slug must contain only lowercase letters, numbers, and hyphens.',
        'blocks.*.name.regex' => 'Block name must start with a letter and contain only lowercase letters, numbers, and underscores.',
        'blocks.*.name.required' => 'Block name is required.',
    ];

    public function boot(PageTemplateService $templateService): void
    {
        $this->templateService = $templateService;
    }

    public function mount(?int $id = null)
    {
        $this->availableLocales = available_locales();
        $this->editingLocale = Page::defaultLocale();

        if ($id) {
            $this->pageId = $id;
            $this->isEdit = true;
            $this->loadPage();
        } else {
            // New page: seed blocks from default template preset
            $this->seedTemplateBlocks();
        }
    }

    protected function loadPage()
    {
        $this->page = Page::with(['blocks' => function ($q) {
            $q->whereNull('parent_block_id')->orderBy('order');
        }])->findOrFail($this->pageId);

        // Populate form fields
        $this->title = $this->page->title;
        $this->slug = $this->page->slug;
        $this->parentId = $this->page->parent_id;
        $this->menuOrder = $this->page->menu_order;
        $this->status = $this->page->status;
        $this->publishedAt = $this->page->published_at?->format('Y-m-d\TH:i');
        $this->template = $this->page->template;
        $this->featuredImage = $this->page->featured_image;

        // SEO
        $seo = $this->page->seo ?? [];
        $this->metaTitle = $seo['meta_title'] ?? '';
        $this->metaDescription = $seo['meta_description'] ?? '';
        $this->ogTitle = $seo['og_title'] ?? '';
        $this->ogDescription = $seo['og_description'] ?? '';
        $this->ogImage = $seo['og_image'] ?? null;

        // Load blocks
        $this->blocks = $this->page->blocks->map(function ($block) {
            $value = $block->value;
            // Decode JSON for specific types
            if (in_array($block->type, ['checkbox', 'gallery', 'posts', 'repeater'])) {
                $value = is_string($value) ? json_decode($value, true) : $value;
                if ($block->type === 'repeater' && ! is_array($value)) {
                    $value = [];
                }
            }

            return [
                'id' => $block->id,
                'name' => $block->name,
                'type' => $block->type,
                'label' => $block->label,
                'value' => $value,
                'options' => $block->options ?? [],
                'order' => $block->order,
                'is_active' => $block->is_active,
                'is_configured' => true, // Existing blocks are already configured
                'children' => $this->loadChildBlocks($block->id),
            ];
        })->toArray();

        $this->manualSlug = true;

        // Hydrate per-locale snapshots from translations JSON for fields the form binds to.
        $translations = $this->page->translations ?? [];
        foreach ($translations as $locale => $fields) {
            if ($locale === Page::defaultLocale()) {
                continue;
            }
            $seo = is_array($fields['seo'] ?? null) ? $fields['seo'] : [];
            $this->localizedSnapshots[$locale] = [
                'title' => $fields['title'] ?? '',
                'slug' => $fields['slug'] ?? '',
                'metaTitle' => $seo['meta_title'] ?? '',
                'metaDescription' => $seo['meta_description'] ?? '',
                'ogTitle' => $seo['og_title'] ?? '',
                'ogDescription' => $seo['og_description'] ?? '',
                'ogImage' => $seo['og_image'] ?? null,
            ];
        }

        // Hydrate per-locale block values. Each block (and repeater child) stores
        // its own translations JSON, indexed at row level. We mirror them into
        // an array keyed by block index so switchLocale can swap quickly.
        $this->hydrateBlockTranslations();
    }

    protected function hydrateBlockTranslations(): void
    {
        $blockRows = $this->page->allBlocks()->get()->keyBy('id');
        $defaultLocale = Page::defaultLocale();

        foreach ($this->blocks as $bi => $block) {
            if (empty($block['id']) || ! isset($blockRows[$block['id']])) {
                continue;
            }

            $rowTrans = $blockRows[$block['id']]->translations ?? [];
            foreach ($rowTrans as $locale => $fields) {
                if ($locale === $defaultLocale) {
                    continue;
                }
                $value = $fields['value'] ?? null;
                // Decode JSON for repeater (whole rows array stored as JSON string)
                if ($block['type'] === 'repeater' && is_string($value)) {
                    $value = json_decode($value, true) ?: [];
                }
                $this->localizedBlockValues[$locale][$bi]['value'] = $value;
            }
        }
    }

    /**
     * Switch the form between locale tabs.
     * Snapshots current form values into the previous locale's slot, then loads
     * the requested locale's values into the form.
     */
    public function switchLocale(string $newLocale): void
    {
        if ($newLocale === $this->editingLocale) {
            return;
        }
        if (! in_array($newLocale, $this->availableLocales, true)) {
            return;
        }

        $prevLocale = $this->editingLocale;

        // 1. Snapshot current Page-level form into the OLD locale's slot
        $this->localizedSnapshots[$prevLocale] = $this->currentLocaleFormSnapshot();

        // 2. Snapshot current block values (only translatable types) into OLD locale's block slot
        $this->snapshotBlocksToLocale($prevLocale);

        // 3. Load NEW locale's Page-level form fields
        $next = $this->localizedSnapshots[$newLocale] ?? [];
        $this->title = $next['title'] ?? '';
        $this->slug = $next['slug'] ?? '';
        $this->metaTitle = $next['metaTitle'] ?? '';
        $this->metaDescription = $next['metaDescription'] ?? '';
        $this->ogTitle = $next['ogTitle'] ?? '';
        $this->ogDescription = $next['ogDescription'] ?? '';
        $this->ogImage = $next['ogImage'] ?? null;

        // 4. Apply NEW locale's block values into $blocks (atomic blocks unchanged)
        $this->applyBlocksFromLocale($newLocale);

        $this->editingLocale = $newLocale;
        $this->resetErrorBag();
    }

    protected function snapshotBlocksToLocale(string $locale): void
    {
        foreach ($this->blocks as $bi => $block) {
            if (! $this->isTranslatableBlockType($block['type'] ?? '')) {
                continue;
            }
            $this->localizedBlockValues[$locale][$bi]['value'] = $block['value'] ?? null;
        }
    }

    protected function applyBlocksFromLocale(string $locale): void
    {
        $defaultLocale = Page::defaultLocale();

        foreach ($this->blocks as $bi => $block) {
            if (! $this->isTranslatableBlockType($block['type'] ?? '')) {
                continue;
            }

            $snap = $this->localizedBlockValues[$locale][$bi]['value'] ?? null;

            if ($snap !== null) {
                $this->blocks[$bi]['value'] = $snap;
            } elseif ($locale === $defaultLocale) {
                // Default locale, no snapshot yet — keep whatever's currently in the form
            } else {
                // Non-default locale with no translation: blank for text types, empty array for repeater
                $this->blocks[$bi]['value'] = ($block['type'] === 'repeater') ? [] : '';
            }
        }
    }

    /**
     * Translatable block types — text fields and repeater (where rows can hold text).
     * Atomic types (number, date, media, etc.) ignore locale.
     */
    protected function isTranslatableBlockType(string $type): bool
    {
        return in_array($type, PageBlock::$translatableTypes, true) || $type === 'repeater';
    }

    protected function currentLocaleFormSnapshot(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'metaTitle' => $this->metaTitle,
            'metaDescription' => $this->metaDescription,
            'ogTitle' => $this->ogTitle,
            'ogDescription' => $this->ogDescription,
            'ogImage' => $this->ogImage,
        ];
    }

    protected function loadChildBlocks(int $parentBlockId): array
    {
        return PageBlock::where('parent_block_id', $parentBlockId)
            ->orderBy('order')
            ->get()
            ->map(function ($block) {
                return [
                    'id' => $block->id,
                    'name' => $block->name,
                    'type' => $block->type,
                    'label' => $block->label,
                    'value' => $block->value,
                    'options' => $block->options ?? [],
                    'order' => $block->order,
                    'is_active' => $block->is_active,
                ];
            })
            ->toArray();
    }

    // === SLUG GENERATION ===

    public function updatedTitle($value)
    {
        if (! $this->manualSlug && ! $this->isEdit) {
            $this->slug = $this->makeUniqueSlug(Str::slug($value));
        }
        $this->hasUnsavedChanges = true;
    }

    public function updatedSlug($value)
    {
        $this->slug = Str::slug($value);
        $this->manualSlug = true;
        $this->hasUnsavedChanges = true;
    }

    public function generateSlug()
    {
        $this->slug = $this->makeUniqueSlug(Str::slug($this->title));
        $this->manualSlug = false;
    }

    protected function makeUniqueSlug(string $slug): string
    {
        if (empty($slug)) {
            return '';
        }

        $original = $slug;
        $counter = 2;

        while (Page::where('slug', $slug)->where('id', '!=', $this->pageId ?? 0)->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    // === TEMPLATE CHANGE HOOK ===

    public function updatedTemplate($value): void
    {
        $this->template = $value;
        $this->hasUnsavedChanges = true;
        $this->seedTemplateBlocks();
    }

    // === BLOCK PRESET SEEDING ===

    /**
     * Add preset blocks from the template schema that don't exist in the form yet.
     * Preset blocks always start in configured mode (no setup needed).
     */
    protected function seedTemplateBlocks(): void
    {
        $schema = $this->templateService->getTemplateSchema($this->template);
        $existingNames = array_column($this->blocks, 'name');

        foreach ($schema as $blockDef) {
            if (in_array($blockDef['name'], $existingNames)) {
                continue; // already in the form
            }

            $this->blocks[] = [
                'id' => null,
                'name' => $blockDef['name'],
                'type' => $blockDef['type'],
                'label' => $blockDef['label'] ?? ucfirst(str_replace('_', ' ', $blockDef['name'])),
                'value' => $blockDef['default'] ?? $this->getDefaultValue($blockDef['type']),
                'options' => $blockDef['options'] ?? $this->getDefaultOptions($blockDef['type']),
                'order' => count($this->blocks),
                'is_active' => true,
                'is_configured' => true, // pre-configured — skip setup mode
                'children' => [],
            ];
        }
    }

    // === BLOCK MANAGEMENT ===

    public function openBlockSelector()
    {
        $this->showBlockSelector = true;
    }

    public function closeBlockSelector()
    {
        $this->showBlockSelector = false;
    }

    public function addBlock(string $blockType)
    {
        $blockConfig = PageBlock::$blockTypes[$blockType] ?? [];

        $this->blocks[] = [
            'id' => null,
            'name' => '',
            'type' => $blockType,
            'label' => $blockConfig['label'] ?? ucfirst($blockType),
            'value' => $this->getDefaultValue($blockType),
            'options' => $this->getDefaultOptions($blockType),
            'order' => count($this->blocks),
            'is_active' => true,
            'is_configured' => false, // Start in config mode
            'children' => [],
        ];

        $this->showBlockSelector = false;
        $this->hasUnsavedChanges = true;
        $this->editingBlockIndex = count($this->blocks) - 1;
    }

    protected function getDefaultValue(string $type): mixed
    {
        return match ($type) {
            'switcher' => false,
            'number' => 0,
            'checkbox', 'gallery', 'posts', 'repeater' => [],
            default => '',
        };
    }

    protected function getDefaultOptions(string $type): array
    {
        return match ($type) {
            'select', 'radio', 'checkbox' => ['choices' => []],
            'number' => ['min' => null, 'max' => null, 'step' => 1],
            'media' => ['allowed_types' => ['image/*'], 'max_size' => 2048],
            'gallery' => ['max_items' => 10],
            'repeater' => ['min_items' => 0, 'max_items' => 10, 'button_label' => 'Add Item'],
            'posts' => ['post_type' => 'post', 'max_items' => 5],
            default => [],
        };
    }

    public function duplicateBlock(int $index)
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        $block = $this->blocks[$index];
        $block['id'] = null;
        $block['name'] = $block['name'].'_copy';
        $block['order'] = count($this->blocks);
        $block['is_configured'] = false; // Put duplicate in config mode

        // Duplicate children for repeaters
        if (isset($block['children'])) {
            $block['children'] = array_map(function ($child) {
                $child['id'] = null;

                return $child;
            }, $block['children']);
        }

        $this->blocks[] = $block;
        $this->hasUnsavedChanges = true;
    }

    public function removeBlock(int $index)
    {
        array_splice($this->blocks, $index, 1);

        // Reorder remaining blocks
        foreach ($this->blocks as $key => $block) {
            $this->blocks[$key]['order'] = $key;
        }

        $this->hasUnsavedChanges = true;
    }

    public function moveBlockUp(int $index)
    {
        if ($index > 0) {
            $temp = $this->blocks[$index];
            $this->blocks[$index] = $this->blocks[$index - 1];
            $this->blocks[$index - 1] = $temp;

            $this->blocks[$index]['order'] = $index;
            $this->blocks[$index - 1]['order'] = $index - 1;

            $this->hasUnsavedChanges = true;
        }
    }

    public function moveBlockDown(int $index)
    {
        if ($index < count($this->blocks) - 1) {
            $temp = $this->blocks[$index];
            $this->blocks[$index] = $this->blocks[$index + 1];
            $this->blocks[$index + 1] = $temp;

            $this->blocks[$index]['order'] = $index;
            $this->blocks[$index + 1]['order'] = $index + 1;

            $this->hasUnsavedChanges = true;
        }
    }

    public function toggleBlockActive(int $index)
    {
        if (isset($this->blocks[$index])) {
            $this->blocks[$index]['is_active'] = ! $this->blocks[$index]['is_active'];
            $this->hasUnsavedChanges = true;
        }
    }

    // === BLOCK CONFIG MODE ===

    public function saveBlockConfig(int $index)
    {
        // Validate block name is set
        if (empty($this->blocks[$index]['name'])) {
            $this->addError("blocks.{$index}.name", 'Block name is required.');

            return;
        }

        // Check for duplicate block names
        foreach ($this->blocks as $i => $block) {
            if ($i !== $index && $block['name'] === $this->blocks[$index]['name']) {
                $this->addError("blocks.{$index}.name", 'Block name must be unique.');

                return;
            }
        }

        $this->blocks[$index]['is_configured'] = true;
        $this->hasUnsavedChanges = true;
    }

    public function editBlockSettings(int $index)
    {
        if (isset($this->blocks[$index])) {
            $this->blocks[$index]['is_configured'] = false;
        }
    }

    // === REPEATER MANAGEMENT ===

    // This adds a FIELD definition to the repeater schema
    public function addRepeaterItem(int $blockIndex)
    {
        if (! isset($this->blocks[$blockIndex]['children'])) {
            $this->blocks[$blockIndex]['children'] = [];
        }

        $this->blocks[$blockIndex]['children'][] = [
            'id' => null,
            'name' => 'field_'.count($this->blocks[$blockIndex]['children']),
            'type' => 'text',
            'label' => 'Field '.(count($this->blocks[$blockIndex]['children']) + 1),
            'value' => '',
            'options' => [],
            'order' => count($this->blocks[$blockIndex]['children']),
            'is_active' => true,
        ];

        $this->hasUnsavedChanges = true;
    }

    public function removeRepeaterItem(int $blockIndex, int $childIndex)
    {
        if (isset($this->blocks[$blockIndex]['children'][$childIndex])) {
            array_splice($this->blocks[$blockIndex]['children'], $childIndex, 1);

            // Reorder
            foreach ($this->blocks[$blockIndex]['children'] as $key => $child) {
                $this->blocks[$blockIndex]['children'][$key]['order'] = $key;
            }

            $this->hasUnsavedChanges = true;
        }
    }

    public function moveRepeaterItemUp(int $blockIndex, int $childIndex)
    {
        if ($childIndex > 0 && isset($this->blocks[$blockIndex]['children'][$childIndex])) {
            $temp = $this->blocks[$blockIndex]['children'][$childIndex];
            $this->blocks[$blockIndex]['children'][$childIndex] = $this->blocks[$blockIndex]['children'][$childIndex - 1];
            $this->blocks[$blockIndex]['children'][$childIndex - 1] = $temp;

            $this->blocks[$blockIndex]['children'][$childIndex]['order'] = $childIndex;
            $this->blocks[$blockIndex]['children'][$childIndex - 1]['order'] = $childIndex - 1;

            $this->hasUnsavedChanges = true;
        }
    }

    public function moveRepeaterItemDown(int $blockIndex, int $childIndex)
    {
        $children = $this->blocks[$blockIndex]['children'] ?? [];
        if ($childIndex < count($children) - 1 && isset($children[$childIndex])) {
            $temp = $this->blocks[$blockIndex]['children'][$childIndex];
            $this->blocks[$blockIndex]['children'][$childIndex] = $this->blocks[$blockIndex]['children'][$childIndex + 1];
            $this->blocks[$blockIndex]['children'][$childIndex + 1] = $temp;

            $this->blocks[$blockIndex]['children'][$childIndex]['order'] = $childIndex;
            $this->blocks[$blockIndex]['children'][$childIndex + 1]['order'] = $childIndex + 1;

            $this->hasUnsavedChanges = true;
        }
    }

    // === NEW: REPEATER DATA ROW MANAGEMENT ===

    public function addRepeaterRow(int $blockIndex)
    {
        if (! isset($this->blocks[$blockIndex]['value']) || ! is_array($this->blocks[$blockIndex]['value'])) {
            $this->blocks[$blockIndex]['value'] = [];
        }

        // Initialize an empty row with keys based on children fields
        $newRow = [];
        foreach ($this->blocks[$blockIndex]['children'] ?? [] as $field) {
            $newRow[$field['name']] = ''; // Default empty value
        }

        $this->blocks[$blockIndex]['value'][] = $newRow;
        $this->hasUnsavedChanges = true;
    }

    public function removeRepeaterRow(int $blockIndex, int $rowIndex)
    {
        if (isset($this->blocks[$blockIndex]['value'][$rowIndex])) {
            array_splice($this->blocks[$blockIndex]['value'], $rowIndex, 1);
            $this->hasUnsavedChanges = true;
        }
    }

    // === CHOICE OPTIONS FOR SELECT/RADIO/CHECKBOX ===

    public function addChoice(int $blockIndex)
    {
        if (! isset($this->blocks[$blockIndex]['options']['choices'])) {
            $this->blocks[$blockIndex]['options']['choices'] = [];
        }

        $this->blocks[$blockIndex]['options']['choices'][] = [
            'label' => '',
            'value' => '',
        ];

        $this->hasUnsavedChanges = true;
    }

    public function removeChoice(int $blockIndex, int $choiceIndex)
    {
        if (isset($this->blocks[$blockIndex]['options']['choices'][$choiceIndex])) {
            array_splice($this->blocks[$blockIndex]['options']['choices'], $choiceIndex, 1);
            $this->hasUnsavedChanges = true;
        }
    }

    // === MEDIA PICKER ===

    public function openMediaPicker(string $field)
    {
        $this->mediaPickerField = $field;
        $this->showMediaPicker = true;
        $this->dispatch('open-media-picker', field: $field);
    }

    #[On('media-selected')]
    public function onMediaSelected($mediaPath)
    {
        if ($this->mediaPickerField === 'featured_image') {
            $this->featuredImage = $mediaPath;
        } elseif ($this->mediaPickerField === 'og_image') {
            $this->ogImage = $mediaPath;
        } elseif (str_starts_with($this->mediaPickerField, 'block_')) {
            $blockIndex = (int) str_replace('block_', '', $this->mediaPickerField);
            if (isset($this->blocks[$blockIndex])) {
                $this->blocks[$blockIndex]['value'] = $mediaPath;
            }
        } elseif (str_starts_with($this->mediaPickerField, 'repeater_')) {
            // Format: repeater_{blockIndex}_{rowIndex}_{fieldName}
            $parts = explode('_', $this->mediaPickerField);
            if (count($parts) >= 4) {
                // remove "repeater"
                array_shift($parts);
                $blockIndex = (int) array_shift($parts);
                $rowIndex = (int) array_shift($parts);
                // field name can contain underscores, so join the rest
                $fieldName = implode('_', $parts);

                if (isset($this->blocks[$blockIndex]['value'][$rowIndex])) {
                    $this->blocks[$blockIndex]['value'][$rowIndex][$fieldName] = $mediaPath;
                }
            }
        }

        $this->hasUnsavedChanges = true;
        $this->showMediaPicker = false;
        $this->mediaPickerField = null;
    }

    public function clearFeaturedImage()
    {
        $this->featuredImage = null;
        $this->hasUnsavedChanges = true;
    }

    public function clearOgImage()
    {
        $this->ogImage = null;
        $this->hasUnsavedChanges = true;
    }

    // === SAVE OPERATIONS ===

    public function save()
    {
        // Mirror current form into the active locale's snapshot before validating /
        // assembling, so the active tab's edits are always persisted.
        $this->localizedSnapshots[$this->editingLocale] = $this->currentLocaleFormSnapshot();
        $this->snapshotBlocksToLocale($this->editingLocale);

        $this->validate();

        // Validate unique block names within the page
        $blockNames = array_column($this->blocks, 'name');
        if (count($blockNames) !== count(array_unique($blockNames))) {
            $this->addError('blocks', 'Block names must be unique within the page.');

            return;
        }

        $defaultLocale = Page::defaultLocale();
        $defaultSnap = $this->localizedSnapshots[$defaultLocale] ?? $this->currentLocaleFormSnapshot();

        // Build default-locale data for the row columns
        $pageData = [
            'title' => $defaultSnap['title'] ?? $this->title,
            'slug' => $defaultSnap['slug'] ?? $this->slug,
            'parent_id' => $this->parentId,
            'menu_order' => $this->menuOrder,
            'status' => $this->status,
            'published_at' => $this->publishedAt ? Carbon::parse($this->publishedAt) : null,
            'author_id' => $this->isEdit ? $this->page->author_id : auth()->id(),
            'template' => $this->template,
            'featured_image' => $this->featuredImage,
            'seo' => array_filter([
                'meta_title' => ($defaultSnap['metaTitle'] ?? '') ?: null,
                'meta_description' => ($defaultSnap['metaDescription'] ?? '') ?: null,
                'og_title' => ($defaultSnap['ogTitle'] ?? '') ?: null,
                'og_description' => ($defaultSnap['ogDescription'] ?? '') ?: null,
                'og_image' => $defaultSnap['ogImage'] ?? null,
            ]),
        ];

        // Build translations JSON from snapshots for non-default locales
        $translations = [];
        foreach ($this->localizedSnapshots as $locale => $snap) {
            if ($locale === $defaultLocale) {
                continue;
            }
            $seo = array_filter([
                'meta_title' => ($snap['metaTitle'] ?? '') ?: null,
                'meta_description' => ($snap['metaDescription'] ?? '') ?: null,
                'og_title' => ($snap['ogTitle'] ?? '') ?: null,
                'og_description' => ($snap['ogDescription'] ?? '') ?: null,
                'og_image' => $snap['ogImage'] ?? null,
            ]);
            $localeFields = array_filter([
                'title' => ($snap['title'] ?? '') ?: null,
                'slug' => ($snap['slug'] ?? '') ?: null,
                'seo' => ! empty($seo) ? $seo : null,
            ], fn ($v) => $v !== null);
            if (! empty($localeFields)) {
                $translations[$locale] = $localeFields;
            }
        }
        $pageData['translations'] = $translations ?: null;

        if ($this->isEdit) {
            $this->page->update($pageData);
        } else {
            $this->page = Page::create($pageData);
            $this->pageId = $this->page->id;
            $this->isEdit = true;
        }

        // Save blocks
        $this->saveBlocks();

        // Create revision
        $this->createRevision();

        $this->hasUnsavedChanges = false;
        $this->lastSavedAt = now()->format('g:i A');

        $this->dispatch('notify', type: 'success', message: 'Page saved successfully!');
    }

    protected function saveBlocks()
    {
        $defaultLocale = Page::defaultLocale();

        // Delete all existing blocks for this page (clean slate)
        $this->page->allBlocks()->delete();

        // Recreate blocks
        foreach ($this->blocks as $index => $blockData) {
            // Resolve the value that goes into the `value` column (always the default locale).
            // - Translatable types: if user is on a non-default tab, default value comes
            //   from the snapshot stashed when they switched away from default.
            // - Atomic types: always use $blockData['value'] — they're identical across locales,
            //   so whatever's currently in the form is canonical.
            $isTranslatable = $this->isTranslatableBlockType($blockData['type'] ?? '');
            if ($isTranslatable && $this->editingLocale !== $defaultLocale) {
                $defaultValue = $this->localizedBlockValues[$defaultLocale][$index]['value']
                    ?? $blockData['value'] ?? '';
            } else {
                $defaultValue = $blockData['value'] ?? '';
            }

            // Encode JSON for collection-style types
            if (in_array($blockData['type'], ['checkbox', 'gallery', 'posts', 'repeater'])) {
                $defaultValue = is_array($defaultValue) ? json_encode($defaultValue) : $defaultValue;
            }

            // Build per-block translations JSON from non-default locale snapshots.
            $blockTranslations = [];
            if ($this->isTranslatableBlockType($blockData['type'] ?? '')) {
                foreach ($this->localizedBlockValues as $locale => $snaps) {
                    if ($locale === $defaultLocale) {
                        continue;
                    }
                    $v = $snaps[$index]['value'] ?? null;
                    if ($v === null || $v === '' || (is_array($v) && empty($v))) {
                        continue;
                    }
                    if ($blockData['type'] === 'repeater' && is_array($v)) {
                        $v = json_encode($v);
                    }
                    $blockTranslations[$locale]['value'] = $v;
                }
            }

            $block = PageBlock::create([
                'page_id' => $this->page->id,
                'parent_block_id' => null,
                'name' => $blockData['name'],
                'type' => $blockData['type'],
                'label' => $blockData['label'] ?? '',
                'value' => $defaultValue,
                'options' => $blockData['options'] ?? [],
                'translations' => $blockTranslations ?: null,
                'order' => $index,
                'is_active' => $blockData['is_active'] ?? true,
            ]);

            // Save repeater children (SCHEMA Definition — no per-locale data)
            if (isset($blockData['children']) && is_array($blockData['children'])) {
                foreach ($blockData['children'] as $childIndex => $childData) {
                    PageBlock::create([
                        'page_id' => $this->page->id,
                        'parent_block_id' => $block->id,
                        'name' => $childData['name'],
                        'type' => $childData['type'],
                        'label' => $childData['label'] ?? '',
                        'value' => '', // Schema Fields don't hold data values
                        'options' => $childData['options'] ?? [],
                        'order' => $childIndex,
                        'is_active' => $childData['is_active'] ?? true,
                    ]);
                }
            }
        }
    }

    protected function createRevision(bool $isAutosave = false)
    {
        $blocksSnapshot = $this->page->allBlocks->map(function ($block) {
            $data = $block->toArray();
            if ($block->type === 'repeater') {
                $data['children'] = $block->childBlocks->map(fn ($child) => $child->toArray())->toArray();
            }

            return $data;
        })->toArray();

        PageRevision::create([
            'page_id' => $this->page->id,
            'user_id' => auth()->id(),
            'title' => $this->page->title,
            'slug' => $this->page->slug,
            'status' => $this->page->status,
            'blocks' => $blocksSnapshot,
            'seo' => $this->page->seo,
            'is_autosave' => $isAutosave,
        ]);
    }

    public function saveAsDraft()
    {
        $this->status = 'draft';
        $this->save();
    }

    public function publish()
    {
        $this->status = 'published';
        $this->publishedAt = $this->publishedAt ?? now()->format('Y-m-d\TH:i');
        $this->save();
    }

    // === AUTOSAVE ===

    public function autosave()
    {
        if (! $this->hasUnsavedChanges || ! $this->isEdit) {
            return;
        }

        // Only autosave if we have a title and at least one block with a name
        if (empty($this->title)) {
            return;
        }

        $this->save();
    }

    // === SEO TOGGLE ===

    public function toggleSeoSettings()
    {
        $this->showSeoSettings = ! $this->showSeoSettings;
    }

    public function render()
    {
        $parentPages = Page::whereNull('parent_id')
            ->when($this->pageId, fn ($q) => $q->where('id', '!=', $this->pageId))
            ->orderBy('title')
            ->get();

        return view('livewire.admin.pages.page-form', [
            'parentPages' => $parentPages,
            'templates' => Page::getTemplates(),
            'blockTypes' => PageBlock::$blockTypes,
            'colorClasses' => PageBlock::$colorClasses,
        ]);
    }
}
