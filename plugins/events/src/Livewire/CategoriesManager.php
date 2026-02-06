<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Plugins\Events\Models\EventCategory;
use Illuminate\Support\Str;
use App\Models\Media;

class CategoriesManager extends Component
{
    use WithPagination, WithFileUploads;

    // Form Fields
    public $name = '';
    public $slug = '';
    public $description = '';
    public $color = '#2563EB';
    public $image;
    public $existingImageId = null;
    
    // Edit Mode
    public $editingCategory = null;

    public function render()
    {
        $categories = EventCategory::with('image')->withCount('events')->orderBy('order')->paginate(20);

        return view('events::livewire.categories-manager', [
            'categories' => $categories,
        ]);
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
            'slug' => 'required|unique:event_categories,slug',
            'description' => 'nullable|string',
            'color' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $imageId = null;
        if ($this->image) {
            $path = $this->image->store('event-categories', 'public');
            $media = Media::create([
                'file_name' => $this->image->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->image->getMimeType(),
                'file_size' => $this->image->getSize(),
                'disk' => 'public',
            ]);
            $imageId = $media->id;
        }

        $maxOrder = EventCategory::max('order') ?? 0;

        EventCategory::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'color' => $this->color,
            'image_id' => $imageId,
            'order' => $maxOrder + 1,
        ]);

        $this->reset(['name', 'slug', 'description', 'color', 'image', 'existingImageId']);
        $this->color = '#2563EB';
        session()->flash('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $this->editingCategory = EventCategory::with('image')->find($id);
        $this->name = $this->editingCategory->name;
        $this->slug = $this->editingCategory->slug;
        $this->description = $this->editingCategory->description;
        $this->color = $this->editingCategory->color;
        $this->existingImageId = $this->editingCategory->image_id;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|min:2',
            'slug' => 'required|unique:event_categories,slug,' . $this->editingCategory->id,
            'description' => 'nullable|string',
            'color' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $imageId = $this->existingImageId;
        if ($this->image) {
            $path = $this->image->store('event-categories', 'public');
            $media = Media::create([
                'file_name' => $this->image->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->image->getMimeType(),
                'file_size' => $this->image->getSize(),
                'disk' => 'public',
            ]);
            $imageId = $media->id;
        }

        $this->editingCategory->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'color' => $this->color,
            'image_id' => $imageId,
        ]);

        $this->cancelEdit();
        session()->flash('success', 'Category updated successfully.');
    }

    public function cancelEdit()
    {
        $this->editingCategory = null;
        $this->reset(['name', 'slug', 'description', 'color', 'image', 'existingImageId']);
        $this->color = '#2563EB';
    }

    public function delete($id)
    {
        EventCategory::find($id)->delete();
        session()->flash('success', 'Category deleted successfully.');
    }

    public function updateOrder($categories)
    {
        foreach ($categories as $index => $categoryId) {
            EventCategory::where('id', $categoryId)->update(['order' => $index]);
        }
        
        session()->flash('success', 'Category order updated successfully.');
    }
}
