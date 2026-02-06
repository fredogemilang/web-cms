<?php

namespace Plugins\Posts\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Plugins\Posts\Models\Category;
use Illuminate\Support\Str;

class CategoriesManager extends Component
{
    use WithPagination;

    // Form Fields
    public $name = '';
    public $slug = '';
    public $parent_id = null;
    public $description = '';
    
    // Edit Mode
    public $editingCategory = null;

    public function render()
    {
        $allCategories = Category::with('parent')->withCount('posts')->orderBy('name')->get();
        $hierarchicalCategories = $this->flattenCategories($allCategories);

        // Manual pagination
        $currentPage = $this->getPage();
        $perPage = 20;
        $categories = new \Illuminate\Pagination\LengthAwarePaginator(
            $hierarchicalCategories->forPage($currentPage, $perPage),
            $hierarchicalCategories->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $parents = Category::where('parent_id', null)->orderBy('name')->get();

        return view('posts::livewire.categories-manager', [
            'categories' => $categories,
            'parents' => $parents,
        ]);
    }

    private function flattenCategories($categories, $parentId = null, $depth = 0)
    {
        $result = collect();
        $items = $categories->where('parent_id', $parentId);

        foreach ($items as $item) {
            $item->depth = $depth;
            $result->push($item);
            $result = $result->merge($this->flattenCategories($categories, $item->id, $depth + 1));
        }

        return $result;
    }

    public function updatedName($value)
    {
        if (!$this->editingCategory) {
            $this->slug = Str::slug($value);
        }
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|min:2',
            'slug' => 'required|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id ?: null,
            'description' => $this->description,
        ]);

        $this->reset(['name', 'slug', 'parent_id', 'description']);
        session()->flash('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $this->editingCategory = Category::find($id);
        $this->name = $this->editingCategory->name;
        $this->slug = $this->editingCategory->slug;
        $this->parent_id = $this->editingCategory->parent_id;
        $this->description = $this->editingCategory->description;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|min:2',
            'slug' => 'required|unique:categories,slug,' . $this->editingCategory->id,
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        $this->editingCategory->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id ?: null,
            'description' => $this->description,
        ]);

        $this->cancelEdit();
        session()->flash('success', 'Category updated successfully.');
    }

    public function cancelEdit()
    {
        $this->editingCategory = null;
        $this->reset(['name', 'slug', 'parent_id', 'description']);
    }

    public function delete($id)
    {
        Category::find($id)->delete();
        session()->flash('success', 'Category deleted successfully.');
    }
}
