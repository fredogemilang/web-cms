<?php

namespace Plugins\Posts\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Plugins\Posts\Models\Post;
use Plugins\Posts\Models\Category;
use Plugins\Posts\Models\Tag;
use Illuminate\Support\Str;

class PostForm extends Component
{
    use WithFileUploads;
    
    #[On('media-selected')]
    public function onMediaSelected($field, $mediaId, $mediaPath, $mediaUrl)
    {
        if ($field === 'featured_image') {
            $this->featured_image = $mediaPath;
        }
    }
    
    #[On('media-removed')]
    public function onMediaRemoved($field)
    {
        if ($field === 'featured_image') {
            $this->featured_image = null;
        }
    }

    public ?Post $post = null;
    public $postId = null;
    
    // Form Fields
    public $title = '';
    public $slug = '';
    public $content = '';
    public $excerpt = '';
    public $status = 'draft';
    public $visibility = 'public';
    public $published_at = null;
    public $featured_image = null;
    public $is_featured = false;
    public $meta_title = '';
    public $meta_description = '';
    public $og_title = '';
    public $og_description = '';
    public $og_image = '';
    
    public $author_id;

    // Relationships
    public $selectedCategories = [];
    public $tags = ''; // Comma separated

    public $password = '';

    public function mount($postId = null)
    {
        if ($postId) {
            $this->postId = $postId;
            $this->post = Post::findOrFail($postId);
            
            $this->title = $this->post->title;
            $this->slug = $this->post->slug;
            $this->content = $this->post->content;
            $this->excerpt = $this->post->excerpt;
            $this->status = $this->post->status;
            $this->visibility = $this->post->visibility ?? 'public';
            $this->password = $this->post->password;
            $this->author_id = $this->post->author_id;
            $this->published_at = $this->post->published_at ? $this->post->published_at->format('Y-m-d\TH:i') : null;
            $this->featured_image = $this->post->featured_image;
            $this->is_featured = $this->post->is_featured;
            
            // Meta Data
            $this->meta_title = $this->post->meta['meta_title'] ?? '';
            $this->meta_description = $this->post->meta['meta_description'] ?? '';
            
            // Open Graph
            $this->og_title = $this->post->meta['og_title'] ?? '';
            $this->og_description = $this->post->meta['og_description'] ?? '';
            $this->og_image = $this->post->meta['og_image'] ?? '';
            
            $this->selectedCategories = $this->post->categories->pluck('id')->toArray();
            $this->tags = $this->post->tags->pluck('name')->implode(', ');
        } else {
            $this->status = 'draft';
            $this->visibility = 'public';
            $this->author_id = auth()->id();
        }
    }

    public function updatedTitle($value)
    {
        if (!$this->postId && empty($this->slug)) {
            $this->slug = $this->ensureUniqueSlug(Str::slug($value));
        }
    }

    protected function ensureUniqueSlug($slug)
    {
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $slugQuery = Post::withTrashed()->where('slug', $slug);

            if ($this->postId) {
                $slugQuery->where('id', '!=', $this->postId);
            }

            if (!$slugQuery->exists()) {
                break;
            }

            $counter++;
            $slug = $originalSlug . '-' . $counter;
        }

        return $slug;
    }

    public function save($status = null)
    {
        try {
            $this->validate([
                'title' => 'required|min:3',
                // 'slug' => 'required|unique:posts,slug,' . $this->postId, // Replaced by manual unique check
                'slug' => 'required',
                'status' => 'required|in:draft,published,scheduled,archived',
                'visibility' => 'required|in:public,private,password',
                'password' => 'required_if:visibility,password',
                'author_id' => 'required|exists:users,id',
                'is_featured' => 'boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'There are validation errors. Please check the form.',
            ]);
            throw $e;
        }

        if ($status) {
            $this->status = $status;
        }

        // Auto-generate excerpt if empty
        if (empty($this->excerpt) && !empty($this->content)) {
            $cleaned = html_entity_decode($this->content);
            $cleaned = strip_tags($cleaned);
            $cleaned = preg_replace('/\s+/', ' ', $cleaned);
            $this->excerpt = Str::limit(trim($cleaned), 155);
        }

        // Handle Image - now using MediaPicker, path is set directly
        $imagePath = $this->featured_image;

        // Ensure slug is unique with auto-increment
        $this->slug = $this->ensureUniqueSlug($this->slug);

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'password' => $this->visibility === 'password' ? $this->password : null,
            'author_id' => $this->author_id,
            'published_at' => $this->status === 'published' && !$this->published_at ? now() : $this->published_at,
            'featured_image' => $imagePath,
            'is_featured' => $this->is_featured,
            'meta' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'og_title' => $this->og_title,
                'og_description' => $this->og_description,
                'og_image' => $this->og_image,
            ],
        ];

        $isNew = !$this->postId;

        if ($this->postId) {
            $this->post->update($data);
            $post = $this->post;
        } else {
            $post = Post::create($data);
            $this->postId = $post->id;
            $this->post = $post;
        }

        // Sync Categories
        $post->categories()->sync($this->selectedCategories);

        // Sync Tags
        if ($this->tags) {
            $tagNames = array_map('trim', explode(',', $this->tags));
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                if (empty($tagName)) continue;
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        } else {
            $post->tags()->detach();
        }

        if ($isNew) {
            session()->flash('success', 'Post created successfully.');
            return redirect()->route('admin.posts.edit', $post->id);
        } else {
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Post updated successfully.']);
            return redirect()->route('admin.posts.edit', $post->id);
        }
    }

    public function delete()
    {
        if ($this->post) {
            $this->post->delete();
            session()->flash('success', 'Post moved to trash.');
            return redirect()->route('admin.posts.index');
        }
    }

    public function removeTag($tagName)
    {
        $tags = array_map('trim', explode(',', $this->tags));
        $tags = array_diff($tags, [$tagName]);
        $this->tags = implode(', ', $tags);
    }

    public function addCategory($name)
    {
        if (empty($name)) return;
        
        $category = Category::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name)]
        );
        
        if (!in_array($category->id, $this->selectedCategories)) {
            $this->selectedCategories[] = $category->id;
        }
    }

    public function addTag($name)
    {
        if (empty($name)) return;
        
        $currentTags = array_filter(array_map('trim', explode(',', $this->tags)));
        if (!in_array($name, $currentTags)) {
            $currentTags[] = $name;
            $this->tags = implode(', ', $currentTags);
        }
    }

    public function render()
    {
        return view('posts::livewire.post-form', [
            'categories' => Category::orderBy('name')->get(),
            'users' => \App\Models\User::orderBy('name')->get(),
        ]);
    }
}
