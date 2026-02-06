<?php

namespace Plugins\Posts\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Plugins\Posts\Models\Post;
use Plugins\Posts\Models\Category;

class BlogList extends Component
{
    use WithPagination;

    public $category = '';
    public $tag = '';

    protected $queryString = [
        'tag' => ['except' => ''],
    ];

    public function mount($category = null)
    {
        if ($category) {
            $this->category = $category;
        } else {
            $this->category = request()->query('category', '');
        }
        $this->tag = request()->query('tag', '');
    }


    public function setCategory($slug)
    {
        $this->category = $slug;
        $this->resetPage();
        
        // Build the new URL
        $url = $slug ? route('posts.category', $slug) : route('posts.index');
        $this->dispatch('update-url', url: $url);
    }

    public function updatedPage()
    {
        $this->dispatch('scroll-to-top');
    }

    public function render()
    {
        $query = Post::where('status', 'published');

        if ($this->category) {
            $query->whereHas('categories', function ($q) {
                $q->where('slug', $this->category);
            });
        }

        if ($this->tag) {
            $query->whereHas('tags', function ($q) {
                $q->where('slug', $this->tag);
            });
        }

        $posts = $query->latest()->paginate(9);
        $categories = Category::all();

        // Use custom pagination view
        return view('posts::livewire.blog-list', [
            'posts' => $posts,
            'categories' => $categories
        ]);
    }

    public function paginationView()
    {
        return 'posts::pagination.custom';
    }
}
