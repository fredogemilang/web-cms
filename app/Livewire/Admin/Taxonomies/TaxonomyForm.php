<?php

namespace App\Livewire\Admin\Taxonomies;

use App\Models\CustomPostType;
use App\Models\CustomTaxonomy;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TaxonomyForm extends Component
{
    public ?int $taxonomyId = null;
    public bool $isEdit = false;

    // General Tab
    public string $name = '';
    public string $singularLabel = '';
    public string $pluralLabel = '';
    public string $slug = '';

    // Settings Tab
    public bool $isHierarchical = true;
    public bool $showInMenu = true;
    public bool $showInRest = true;
    public array $postTypes = [];

    // UI State

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/'],
            'singularLabel' => 'required|string|max:100',
            'pluralLabel' => 'required|string|max:100',
            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z][a-z0-9-]*$/',
                Rule::unique('custom_taxonomies', 'slug')->ignore($this->taxonomyId),
            ],
            'isHierarchical' => 'boolean',
            'showInMenu' => 'boolean',
            'showInRest' => 'boolean',
            'postTypes' => 'array',
        ];
    }

    protected $messages = [
        'name.regex' => 'Name must start with a letter and contain only lowercase letters, numbers, and underscores.',
        'slug.regex' => 'Slug must start with a letter and contain only lowercase letters, numbers, and hyphens.',
    ];

    public function mount(?int $id = null)
    {
        if ($id) {
            $this->taxonomyId = $id;
            $this->isEdit = true;
            $this->loadTaxonomy();
        }
    }

    protected function loadTaxonomy()
    {
        $taxonomy = CustomTaxonomy::findOrFail($this->taxonomyId);
        
        $this->name = $taxonomy->name;
        $this->singularLabel = $taxonomy->singular_label;
        $this->pluralLabel = $taxonomy->plural_label;
        $this->slug = $taxonomy->slug;
        $this->isHierarchical = $taxonomy->is_hierarchical;
        $this->showInMenu = $taxonomy->show_in_menu;
        $this->showInRest = $taxonomy->show_in_rest;
        $this->postTypes = $taxonomy->post_types ?? [];
    }

    public function updatedSingularLabel($value)
    {
        if (empty($this->pluralLabel)) {
            $this->pluralLabel = Str::plural($value);
        }
        if (empty($this->name)) {
            $this->name = Str::snake($value);
        }
        if (empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSlug($value)
    {
        // Auto-generate name if empty
        if (empty($this->name)) {
            $this->name = str_replace('-', '_', $value);
        }
    }


    public function togglePostType(string $slug)
    {
        if (in_array($slug, $this->postTypes)) {
            $this->postTypes = array_values(array_filter($this->postTypes, fn($s) => $s !== $slug));
        } else {
            $this->postTypes[] = $slug;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'singular_label' => $this->singularLabel,
            'plural_label' => $this->pluralLabel,
            'slug' => $this->slug,
            'is_hierarchical' => $this->isHierarchical,
            'show_in_menu' => $this->showInMenu,
            'show_in_rest' => $this->showInRest,
            'post_types' => $this->postTypes,
        ];

        if ($this->isEdit && !$this->isHierarchical) {
            $hasRelationships = \App\Models\TaxonomyTerm::where('taxonomy_id', $this->taxonomyId)
                ->whereNotNull('parent_id')
                ->exists();

            if ($hasRelationships) {
                $this->addError('isHierarchical', 'Cannot change to Flat because this taxonomy contains hierarchical terms (parent/child relationships). Please remove relationships first.');
                return;
            }
        }

        if ($this->isEdit) {
            $taxonomy = CustomTaxonomy::findOrFail($this->taxonomyId);
            $taxonomy->update($data);
        } else {
            CustomTaxonomy::create($data);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEdit 
                ? "Taxonomy '{$this->pluralLabel}' updated successfully."
                : "Taxonomy '{$this->pluralLabel}' created successfully.",
        ]);

        return redirect()->route('admin.taxonomies.index');
    }

    public function render()
    {
        $availablePostTypes = CustomPostType::active()->get();

        return view('livewire.admin.taxonomies.taxonomy-form', [
            'availablePostTypes' => $availablePostTypes,
        ]);
    }
}
