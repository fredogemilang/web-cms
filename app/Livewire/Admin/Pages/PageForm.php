<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageRevision;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PageForm extends Component
{
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

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages', 'slug')->ignore($this->pageId),
            ],
            'status' => 'required|in:draft,published,scheduled,private',
            'parentId' => 'nullable|exists:pages,id',
            'template' => 'required|string',
            'publishedAt' => 'nullable|date',
            'blocks.*.name' => 'required|string|max:255|regex:/^[a-z][a-z0-9_]*$/',
            'blocks.*.type' => 'required|in:' . implode(',', array_keys(PageBlock::$blockTypes)),
        ];
    }

    protected $messages = [
        'slug.regex' => 'Slug must contain only lowercase letters, numbers, and hyphens.',
        'blocks.*.name.regex' => 'Block name must start with a letter and contain only lowercase letters, numbers, and underscores.',
        'blocks.*.name.required' => 'Block name is required.',
    ];

    public function mount(?int $id = null)
    {
        if ($id) {
            $this->pageId = $id;
            $this->isEdit = true;
            $this->loadPage();
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
                if ($block->type === 'repeater' && !is_array($value)) {
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
        if (!$this->manualSlug && !$this->isEdit) {
            $this->slug = Str::slug($value);
        }
        $this->hasUnsavedChanges = true;
    }

    public function updatedSlug($value)
    {
        $this->manualSlug = true;
        $this->hasUnsavedChanges = true;
    }

    public function generateSlug()
    {
        $this->slug = Str::slug($this->title);
        $this->manualSlug = false;
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
        if (!isset($this->blocks[$index])) {
            return;
        }

        $block = $this->blocks[$index];
        $block['id'] = null;
        $block['name'] = $block['name'] . '_copy';
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
            $this->blocks[$index]['is_active'] = !$this->blocks[$index]['is_active'];
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
        if (!isset($this->blocks[$blockIndex]['children'])) {
            $this->blocks[$blockIndex]['children'] = [];
        }

        $this->blocks[$blockIndex]['children'][] = [
            'id' => null,
            'name' => 'field_' . count($this->blocks[$blockIndex]['children']),
            'type' => 'text',
            'label' => 'Field ' . (count($this->blocks[$blockIndex]['children']) + 1),
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
        if (!isset($this->blocks[$blockIndex]['value']) || !is_array($this->blocks[$blockIndex]['value'])) {
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
        if (!isset($this->blocks[$blockIndex]['options']['choices'])) {
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

    // === SAVE OPERATIONS ===

    public function save()
    {
        $this->validate();

        // Validate unique block names within the page
        $blockNames = array_column($this->blocks, 'name');
        if (count($blockNames) !== count(array_unique($blockNames))) {
            $this->addError('blocks', 'Block names must be unique within the page.');
            return;
        }

        $pageData = [
            'title' => $this->title,
            'slug' => $this->slug,
            'parent_id' => $this->parentId,
            'menu_order' => $this->menuOrder,
            'status' => $this->status,
            'published_at' => $this->publishedAt ? Carbon::parse($this->publishedAt) : null,
            'author_id' => $this->isEdit ? $this->page->author_id : auth()->id(),
            'template' => $this->template,
            'featured_image' => $this->featuredImage,
            'seo' => array_filter([
                'meta_title' => $this->metaTitle ?: null,
                'meta_description' => $this->metaDescription ?: null,
                'og_title' => $this->ogTitle ?: null,
                'og_description' => $this->ogDescription ?: null,
                'og_image' => $this->ogImage,
            ]),
        ];

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
        // Delete all existing blocks for this page
        $this->page->allBlocks()->delete();

        // Recreate blocks
        foreach ($this->blocks as $index => $blockData) {
            $value = $blockData['value'] ?? '';
            // Encode JSON for specific types
            if (in_array($blockData['type'], ['checkbox', 'gallery', 'posts', 'repeater'])) {
                $value = is_array($value) ? json_encode($value) : $value;
            }

            $block = PageBlock::create([
                'page_id' => $this->page->id,
                'parent_block_id' => null,
                'name' => $blockData['name'],
                'type' => $blockData['type'],
                'label' => $blockData['label'] ?? '',
                'value' => $value,
                'options' => $blockData['options'] ?? [],
                'order' => $index,
                'is_active' => $blockData['is_active'] ?? true,
            ]);

            // Save repeater children (SCHEMA Definition)
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
                $data['children'] = $block->childBlocks->map(fn($child) => $child->toArray())->toArray();
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
        if (!$this->hasUnsavedChanges || !$this->isEdit) {
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
        $this->showSeoSettings = !$this->showSeoSettings;
    }

    public function render()
    {
        $parentPages = Page::whereNull('parent_id')
            ->when($this->pageId, fn($q) => $q->where('id', '!=', $this->pageId))
            ->orderBy('title')
            ->get();

        return view('livewire.admin.pages.page-form', [
            'parentPages' => $parentPages,
            'templates' => Page::$templates,
            'blockTypes' => PageBlock::$blockTypes,
            'colorClasses' => PageBlock::$colorClasses,
        ]);
    }
}
