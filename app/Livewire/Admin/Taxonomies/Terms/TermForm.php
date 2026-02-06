<?php

namespace App\Livewire\Admin\Taxonomies\Terms;

use App\Models\CustomTaxonomy;
use App\Models\TaxonomyTerm;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Str;

use Livewire\Attributes\On;

class TermForm extends Component
{
    public CustomTaxonomy $taxonomy;
    public ?int $termId = null;
    public bool $isEdit = false;
    public bool $inline = false;

    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public ?int $parentId = null;
    public int $order = 0;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxonomy_terms', 'slug')
                    ->where('taxonomy_id', $this->taxonomy->id)
                    ->ignore($this->termId),
            ],
            'description' => 'nullable|string|max:500',
            'parentId' => 'nullable|integer|exists:taxonomy_terms,id',
            'order' => 'integer|min:0',
        ];
    }

    public function mount(CustomTaxonomy $taxonomy, ?int $id = null)
    {
        $this->taxonomy = $taxonomy;

        if ($id) {
            $this->termId = $id;
            $this->isEdit = true;
            $this->loadTerm();
        }
    }

    protected function loadTerm()
    {
        $term = TaxonomyTerm::where('taxonomy_id', $this->taxonomy->id)->findOrFail($this->termId);
        
        $this->name = $term->name;
        $this->slug = $term->slug;
        $this->description = $term->description ?? '';
        $this->parentId = $term->parent_id;
        $this->order = $term->order;
    }

    public function updatedName($value)
    {
        if (!$this->isEdit && empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'taxonomy_id' => $this->taxonomy->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'parent_id' => $this->parentId,
            'order' => $this->order,
        ];

        if ($this->isEdit) {
            $term = TaxonomyTerm::findOrFail($this->termId);
            $term->update($data);
        } else {
            $term = TaxonomyTerm::create($data);
        }

        if ($this->inline) {
            $this->cancelEdit();
            $this->dispatch('term-saved');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Term '{$term->name}' " . ($this->isEdit ? 'updated' : 'created') . " successfully.",
            ]);
            return;
        }

        $this->termId = $term->id;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEdit 
                ? "Term '{$this->name}' updated successfully."
                : "Term '{$this->name}' created successfully.",
        ]);

        return redirect()->route('admin.taxonomies.terms.index', $this->taxonomy->id);
    }

    #[On('edit-term')]
    public function editTerm(int $id)
    {
        $term = TaxonomyTerm::findOrFail($id);
        $this->termId = $term->id;
        $this->name = $term->name;
        $this->slug = $term->slug;
        $this->description = $term->description ?? '';
        $this->parentId = $term->parent_id;
        $this->order = $term->order ?? 0;
        $this->isEdit = true;
        
        $this->resetValidation();
    }

    public function cancelEdit()
    {
        $this->reset(['termId', 'name', 'slug', 'description', 'parentId', 'order']);
        $this->isEdit = false;
        $this->resetValidation();
    }

    public function render()
    {
        $possibleParents = TaxonomyTerm::where('taxonomy_id', $this->taxonomy->id)
            ->where('id', '!=', $this->termId)
            ->orderBy('name')
            ->get();

        if ($this->inline) {
            return view('livewire.admin.taxonomies.terms.term-form-inline', [
                'possibleParents' => $possibleParents,
            ]);
        }

        return view('livewire.admin.taxonomies.terms.term-form', [
            'possibleParents' => $possibleParents,
        ]);
    }
}
