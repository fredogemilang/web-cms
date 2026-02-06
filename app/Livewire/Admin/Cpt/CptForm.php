<?php

namespace App\Livewire\Admin\Cpt;

use App\Models\CustomPostType;
use App\Models\MetaField;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CptForm extends Component
{
    public ?int $cptId = null;
    public bool $isEdit = false;

    // General Tab
    public string $name = '';
    public string $singularLabel = '';
    public string $pluralLabel = '';
    public string $slug = '';
    public string $description = '';
    public string $icon = 'article';

    // Settings Tab
    public bool $isHierarchical = false;
    public bool $showInMenu = true;
    public bool $showInRest = true;
    public bool $hasArchive = true;
    public array $supports = ['title', 'editor', 'thumbnail', 'excerpt', 'author'];
    public array $taxonomies = [];
    public array $metaBoxes = [];
    public array $newMetaBox = [
        'id' => '',
        'title' => '',
        'context' => 'normal',
    ];
    public bool $showMetaBoxModal = false;
    public ?int $editingMetaBoxIndex = null;
    public array $openMetaBoxes = [];
    public bool $showDeleteMetaBoxModal = false;
    public ?int $metaBoxToDeleteIndex = null;
    public bool $manualMetaBoxId = false;

    // Meta Fields
    public array $metaFields = [];

    // UI State
    public string $activeTab = 'general';

    public function updatedNewMetaBox($value, $key)
    {
        if ($this->showMetaBoxModal) {
            if ($key === 'title') {
                if (!$this->manualMetaBoxId) {
                    $this->newMetaBox['id'] = Str::slug($value, '_');
                }
            } elseif ($key === 'id') {
                $this->manualMetaBoxId = true;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:50', 
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('custom_post_types', 'name')->ignore($this->cptId),
            ],
            'singularLabel' => 'required|string|max:100',
            'pluralLabel' => 'required|string|max:100',
            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z][a-z0-9-]*$/',
                Rule::unique('custom_post_types', 'slug')->ignore($this->cptId),
            ],
            'description' => 'nullable|string|max:500',
            'icon' => 'required|string|max:50',
            'isHierarchical' => 'boolean',
            'showInMenu' => 'boolean',
            'showInRest' => 'boolean',
            'hasArchive' => 'boolean',
            'supports' => 'array',
        ];

        if ($this->showMetaBoxModal) {
            $rules['newMetaBox.title'] = 'required|string|max:100';
            $rules['newMetaBox.id'] = 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/';
            $rules['newMetaBox.context'] = 'required|in:normal,side,advanced';
        }

        return $rules;
    }

    protected $messages = [
        'name.regex' => 'Name must start with a letter and contain only lowercase letters, numbers, and underscores.',
        'slug.regex' => 'Slug must start with a letter and contain only lowercase letters, numbers, and hyphens.',
    ];

    public function mount(?int $id = null)
    {
        if ($id) {
            $this->cptId = $id;
            $this->isEdit = true;
            $this->loadCpt();
        }
    }

    protected function loadCpt()
    {
        $cpt = CustomPostType::with('metaFields')->findOrFail($this->cptId);
        
        $this->name = $cpt->name;
        $this->singularLabel = $cpt->singular_label;
        $this->pluralLabel = $cpt->plural_label;
        $this->slug = $cpt->slug;
        $this->description = $cpt->description ?? '';
        $this->icon = $cpt->icon;
        $this->isHierarchical = $cpt->is_hierarchical;
        $this->showInMenu = $cpt->show_in_menu;
        $this->showInRest = $cpt->show_in_rest;
        $this->hasArchive = $cpt->has_archive;
        $this->supports = $cpt->supports ?? [];
        
        $this->metaFields = $cpt->metaFields->map(function ($field) {
            return [
                'id' => $field->id,
                'name' => $field->name,
                'label' => $field->label,
                'type' => $field->type,
                'description' => $field->description ?? '',
                'is_required' => $field->is_required,
                'options' => array_replace_recursive([
                    'conditional_logic' => [
                        'enabled' => false,
                        'relation' => 'all',
                        'rules' => []
                    ]
                ], $field->options ?? []),
                'field_group' => $field->field_group ?? '',
                'order' => $field->order,
            ];
        })->toArray();

        $this->taxonomies = $cpt->taxonomies()->pluck('slug')->toArray();
        $this->metaBoxes = $cpt->settings['meta_boxes'] ?? [];
        
        // Initialize open metaboxes
        foreach ($this->metaBoxes as $box) {
            $this->openMetaBoxes[$box['id']] = false;
        }
    }

    // Auto-generation flags
    public bool $manualName = false;
    public bool $manualPlural = false;
    public bool $manualSlug = false;

    public function updatedName($value)
    {
        $this->manualName = true;
        $this->validateOnly('name');
    }

    public function updatedPluralLabel($value)
    {
        $this->manualPlural = true;
    }

    public function updatedSlug($value)
    {
        $this->manualSlug = true;
        $this->validateOnly('slug');
    }

    public function updatedSingularLabel($value)
    {
        if ($this->isEdit) {
            return;
        }

        // Auto-generate Plural Name if not manually edited
        if (!$this->manualPlural && !empty($value)) {
            $this->pluralLabel = Str::plural($value);
        }

        // Auto-generate Name (Internal) if not manually edited
        if (!$this->manualName && !empty($value)) {
            $this->name = Str::slug($value, '_');
            $this->validateOnly('name');
        }

        // Auto-generate Slug if not manually edited
        if (!$this->manualSlug && !empty($value)) {
            $this->slug = Str::slug($value);
            $this->validateOnly('slug');
        }
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleSupport(string $feature)
    {
        if (in_array($feature, $this->supports)) {
            $this->supports = array_values(array_filter($this->supports, fn($s) => $s !== $feature));
        } else {
            $this->supports[] = $feature;
        }
    }

    public function toggleTaxonomy(string $slug)
    {
        if (in_array($slug, $this->taxonomies)) {
            $this->taxonomies = array_values(array_filter($this->taxonomies, fn($s) => $s !== $slug));
        } else {
            $this->taxonomies[] = $slug;
        }
    }

    public function selectIcon(string $icon)
    {
        $this->icon = $icon;
    }

    // Meta Box Management
    public function openMetaBoxModal(?int $index = null)
    {
        if ($index !== null && isset($this->metaBoxes[$index])) {
            $this->editingMetaBoxIndex = $index;
            $this->newMetaBox = $this->metaBoxes[$index];
            $this->manualMetaBoxId = true;
        } else {
            $this->editingMetaBoxIndex = null;
            $this->newMetaBox = [
                'id' => '',
                'title' => '',
                'context' => 'normal',
            ];
            $this->manualMetaBoxId = false;
        }
        $this->showMetaBoxModal = true;
    }

    public function closeMetaBoxModal()
    {
        $this->showMetaBoxModal = false;
        $this->editingMetaBoxIndex = null;
    }

    public function saveMetaBox()
    {
        $this->validate([
            'newMetaBox.id' => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'newMetaBox.title' => 'required|string|max:100',
            'newMetaBox.context' => 'required|in:normal,side,advanced',
        ]);

        if ($this->editingMetaBoxIndex !== null) {
            $this->metaBoxes[$this->editingMetaBoxIndex] = $this->newMetaBox;
        } else {
            $this->metaBoxes[] = $this->newMetaBox;
        }

        $this->closeMetaBoxModal();
    }

    public function confirmDeleteMetaBox(int $index)
    {
        $this->metaBoxToDeleteIndex = $index;
        $this->showDeleteMetaBoxModal = true;
    }

    public function cancelDeleteMetaBox()
    {
        $this->showDeleteMetaBoxModal = false;
        $this->metaBoxToDeleteIndex = null;
    }

    public function deleteMetaBox(bool $keepFields = false)
    {
        if ($this->metaBoxToDeleteIndex === null) return;

        $boxId = $this->metaBoxes[$this->metaBoxToDeleteIndex]['id'];

        if ($keepFields) {
            // Move fields to uncategorized
            foreach ($this->metaFields as &$field) {
                if (($field['field_group'] ?? '') === $boxId) {
                    $field['field_group'] = '';
                }
            }
        } else {
            // Delete fields
            $this->metaFields = array_values(array_filter($this->metaFields, function ($field) use ($boxId) {
                return ($field['field_group'] ?? '') !== $boxId;
            }));
        }

        // Remove the box
        unset($this->metaBoxes[$this->metaBoxToDeleteIndex]);
        $this->metaBoxes = array_values($this->metaBoxes);
        unset($this->openMetaBoxes[$boxId]);

        $this->cancelDeleteMetaBox();
    }

    public function toggleMetaBoxSettings(string $boxId)
    {
        $this->openMetaBoxes[$boxId] = !($this->openMetaBoxes[$boxId] ?? false);
    }

    // Meta Fields Management
    public function addField(?string $fieldGroup = null)
    {
        $this->metaFields[] = [
            'name' => '',
            'label' => '',
            'type' => 'text',
            'description' => '',
            'is_required' => false,
            'field_group' => $fieldGroup ?? '',
            'options' => [
                'conditional_logic' => [
                    'enabled' => false,
                    'relation' => 'all',
                    'rules' => []
                ],
                'options_list' => [],
                'repeater_fields' => []
            ],
            'order' => count($this->metaFields),
        ];
    }

    public function addConditionalRule(int $fieldIndex)
    {
        if (!isset($this->metaFields[$fieldIndex]['options']['conditional_logic'])) {
            $this->metaFields[$fieldIndex]['options']['conditional_logic'] = [
                'enabled' => true,
                'relation' => 'all',
                'rules' => []
            ];
        }
        
        $this->metaFields[$fieldIndex]['options']['conditional_logic']['rules'][] = [
            'field' => '',
            'operator' => 'equals',
            'value' => ''
        ];
    }

    public function removeConditionalRule(int $fieldIndex, int $ruleIndex)
    {
        unset($this->metaFields[$fieldIndex]['options']['conditional_logic']['rules'][$ruleIndex]);
        $this->metaFields[$fieldIndex]['options']['conditional_logic']['rules'] = array_values(
            $this->metaFields[$fieldIndex]['options']['conditional_logic']['rules']
        );
    }

    public function removeField(int $index)
    {
        unset($this->metaFields[$index]);
        $this->metaFields = array_values($this->metaFields);
        
        // Re-order
        foreach ($this->metaFields as $i => &$field) {
            $field['order'] = $i;
        }
    }

    public function moveFieldUp(int $index)
    {
        if ($index > 0) {
            [$this->metaFields[$index - 1], $this->metaFields[$index]] = 
            [$this->metaFields[$index], $this->metaFields[$index - 1]];
            
            $this->metaFields[$index - 1]['order'] = $index - 1;
            $this->metaFields[$index]['order'] = $index;
        }
    }

    public function moveFieldDown(int $index)
    {
        if ($index < count($this->metaFields) - 1) {
            [$this->metaFields[$index], $this->metaFields[$index + 1]] = 
            [$this->metaFields[$index + 1], $this->metaFields[$index]];
            
            $this->metaFields[$index]['order'] = $index;
            $this->metaFields[$index + 1]['order'] = $index + 1;
        }
    }

    // Field Options Management (Select, Radio, Checkbox)
    public function addFieldOption(int $fieldIndex)
    {
        if (!isset($this->metaFields[$fieldIndex]['options']['options_list'])) {
            $this->metaFields[$fieldIndex]['options']['options_list'] = [];
        }

        $this->metaFields[$fieldIndex]['options']['options_list'][] = [
            'label' => '',
            'value' => '',
            'is_default' => false,
        ];
    }

    public function removeFieldOption(int $fieldIndex, int $optionIndex)
    {
        unset($this->metaFields[$fieldIndex]['options']['options_list'][$optionIndex]);
        $this->metaFields[$fieldIndex]['options']['options_list'] = array_values(
            $this->metaFields[$fieldIndex]['options']['options_list']
        );
    }

    // Repeater Fields Management
    public function addRepeaterField(int $parentFetcherIndex)
    {
        if (!isset($this->metaFields[$parentFetcherIndex]['options']['repeater_fields'])) {
            $this->metaFields[$parentFetcherIndex]['options']['repeater_fields'] = [];
        }

        $this->metaFields[$parentFetcherIndex]['options']['repeater_fields'][] = [
            'name' => '',
            'label' => '',
            'type' => 'text',
            'width' => '100', // width in percentage
            'options' => [
                'options_list' => [], // ensure nested options work if we go recursive later (simple for now)
            ], 
        ];
    }

    public function removeRepeaterField(int $parentFieldIndex, int $subFieldIndex)
    {
        unset($this->metaFields[$parentFieldIndex]['options']['repeater_fields'][$subFieldIndex]);
        $this->metaFields[$parentFieldIndex]['options']['repeater_fields'] = array_values(
            $this->metaFields[$parentFieldIndex]['options']['repeater_fields']
        );
    }

    public function addRepeaterFieldOption(int $fieldIndex, int $subFieldIndex)
    {
        // Ensure options array exists
        if (!isset($this->metaFields[$fieldIndex]['options']['repeater_fields'][$subFieldIndex]['options']['options_list'])) {
             // If options is just a string or missing, initialize it
             $this->metaFields[$fieldIndex]['options']['repeater_fields'][$subFieldIndex]['options']['options_list'] = [];
        }

        $this->metaFields[$fieldIndex]['options']['repeater_fields'][$subFieldIndex]['options']['options_list'][] = [
            'label' => '',
            'value' => '',
            'is_default' => false,
        ];
    }

    public function removeRepeaterFieldOption(int $fieldIndex, int $subFieldIndex, int $optionIndex)
    {
        unset($this->metaFields[$fieldIndex]['options']['repeater_fields'][$subFieldIndex]['options']['options_list'][$optionIndex]);
        $this->metaFields[$fieldIndex]['options']['repeater_fields'][$subFieldIndex]['options']['options_list'] = array_values(
            $this->metaFields[$fieldIndex]['options']['repeater_fields'][$subFieldIndex]['options']['options_list']
        );
    }


    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'singular_label' => $this->singularLabel,
            'plural_label' => $this->pluralLabel,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'icon' => $this->icon,
            'is_hierarchical' => $this->isHierarchical,
            'show_in_menu' => $this->showInMenu,
            'show_in_rest' => $this->showInRest,
            'has_archive' => $this->hasArchive,
            'supports' => $this->supports,
            'settings' => array_merge($this->isEdit ? (CustomPostType::find($this->cptId)->settings ?? []) : [], [
                'meta_boxes' => $this->metaBoxes,
            ]),
        ];

        if ($this->isEdit) {
            $cpt = CustomPostType::findOrFail($this->cptId);
            $cpt->update($data);
        } else {
            $cpt = CustomPostType::create($data);
            $this->cptId = $cpt->id;
        }

        // Sync meta fields
        $this->syncMetaFields($cpt);

        // Sync taxonomies
        $this->syncTaxonomies($cpt);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEdit 
                ? "Post type '{$this->pluralLabel}' updated successfully."
                : "Post type '{$this->pluralLabel}' created successfully.",
        ]);


        if (!$this->isEdit) {
            return redirect()->route('admin.cpt.index');
        }
    }

    protected function syncMetaFields(CustomPostType $cpt)
    {
        $existingIds = [];

        foreach ($this->metaFields as $fieldData) {
            if (isset($fieldData['id'])) {
                // Update existing field
                $field = MetaField::find($fieldData['id']);
                if ($field) {
                    $field->update([
                        'name' => $fieldData['name'],
                        'label' => $fieldData['label'],
                        'type' => $fieldData['type'],
                        'description' => $fieldData['description'] ?? null,
                        'is_required' => $fieldData['is_required'] ?? false,
                        'options' => $fieldData['options'] ?? null,
                        'field_group' => $fieldData['field_group'] ?? null,
                        'order' => $fieldData['order'] ?? 0,
                    ]);
                    $existingIds[] = $field->id;
                }
            } else {
                // Create new field
                $field = $cpt->metaFields()->create([
                    'name' => $fieldData['name'],
                    'label' => $fieldData['label'],
                    'type' => $fieldData['type'],
                    'description' => $fieldData['description'] ?? null,
                    'is_required' => $fieldData['is_required'] ?? false,
                    'options' => $fieldData['options'] ?? null,
                    'field_group' => $fieldData['field_group'] ?? null,
                    'order' => $fieldData['order'] ?? 0,
                ]);
                $existingIds[] = $field->id;
            }
        }

        // Delete removed fields
        $cpt->metaFields()->whereNotIn('id', $existingIds)->delete();
    }

    protected function syncTaxonomies(CustomPostType $cpt)
    {
        $allTaxonomies = \App\Models\CustomTaxonomy::all();
        
        foreach ($allTaxonomies as $taxonomy) {
            if (in_array($taxonomy->slug, $this->taxonomies)) {
                $taxonomy->attachToPostType($cpt->slug);
            } else {
                $taxonomy->detachFromPostType($cpt->slug);
            }
        }
    }

    public function delete()
    {
        if (!$this->isEdit || !$this->cptId) {
            return;
        }

        $cpt = CustomPostType::findOrFail($this->cptId);
        $name = $cpt->plural_label;
        
        // Delete associated meta fields
        $cpt->metaFields()->delete();
        $cpt->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Post type '{$name}' has been deleted.",
        ]);

        return redirect()->route('admin.cpt.index');
    }

    public function render()
    {
        return view('livewire.admin.cpt.cpt-form', [
            'availableSupports' => CustomPostType::$availableSupports,
            'availableTaxonomies' => \App\Models\CustomTaxonomy::active()->get(),
            'fieldTypes' => MetaField::$fieldTypes,
        ]);
    }
}
