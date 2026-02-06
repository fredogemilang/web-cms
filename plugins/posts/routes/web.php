<?php

use Illuminate\Support\Facades\Route;

$adminPath = config('admin.path', config('cms.path', 'admin'));

Route::middleware(['web', 'auth', 'permission:posts.view'])->prefix("{$adminPath}/posts")->name('admin.posts.')->group(function () {
    
    // Posts
    Route::get('/', function () {
        return view('posts::index');
    })->name('index');

    Route::get('/create', function () {
        return view('posts::create');
    })->name('create')->middleware('permission:posts.create');

    Route::get('/{id}/edit', function ($id) {
        return view('posts::edit', ['id' => $id]);
    })->name('edit')->middleware('permission:posts.edit');

    // Categories
    Route::get('/categories', function () {
        return view('posts::categories.index');
    })->name('categories')->middleware('permission:categories.view');

    // Tags
    Route::get('/tags', function () {
        return view('posts::tags.index');
    })->name('tags')->middleware('permission:tags.view');

    // Settings
    Route::get('/settings', function () {
        return view('posts::settings');
    })->name('settings')->middleware('permission:posts.view'); // Reusing view permission

    // WordPress Migration
    Route::get('/wordpress-migration', function () {
        return view('posts::wordpress-migration');
    })->name('wordpress-migration')->middleware('permission:posts.create');

});

// Public Routes
Route::middleware(['web'])->group(function () {
    $archiveSlug = \Plugins\Posts\Models\Setting::get('archive_slug', 'blog');
    
    // Blog Index
    Route::get("/{$archiveSlug}", function () {
        $featuredPosts = \Plugins\Posts\Models\Post::where('status', 'published')
            ->where('is_featured', true)
            ->latest()
            ->take(4)
            ->get();
            
        return view('iccom::posts.index', compact('featuredPosts'));
    })->name('posts.index');

    // Category Index
    Route::get("/{$archiveSlug}/category/{category}", function ($category) {
        $featuredPosts = \Plugins\Posts\Models\Post::where('status', 'published')
            ->where('is_featured', true)
            ->latest()
            ->take(4)
            ->get();
            
        return view('iccom::posts.index', compact('featuredPosts', 'category'));
    })->name('posts.category');

    Route::get("/{$archiveSlug}/{slug}", function ($slug) {
        $post = \Plugins\Posts\Models\Post::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $dateFormat = \Plugins\Posts\Models\Setting::get('date_format', 'M d, Y');
        $enableComments = (bool) \Plugins\Posts\Models\Setting::get('enable_comments', true);
        $closeCommentsDays = (int) \Plugins\Posts\Models\Setting::get('close_comments_days', 0);

        // Theme-aware view resolution
        $viewName = 'iccom::posts.single';

        return view($viewName, [
            'post' => $post,
            'entry' => $post,  // Alias for theme compatibility
            'dateFormat' => $dateFormat,
            'enableComments' => $enableComments,
            'closeCommentsDays' => $closeCommentsDays,
        ]);
    })->name('posts.show');
});
