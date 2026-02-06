<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $post->meta['meta_title'] ?? $post->title }}</title>
    <meta name="description" content="{{ $post->meta['meta_description'] ?? $post->excerpt }}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $post->meta['og_title'] ?? $post->title }}">
    <meta property="og:description" content="{{ $post->meta['og_description'] ?? $post->excerpt }}">
    @if(!empty($post->meta['og_image']))
    <meta property="og:image" content="{{ $post->meta['og_image'] }}">
    @elseif($post->featured_image)
    <meta property="og:image" content="{{ asset('storage/' . $post->featured_image) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">
    <div class="max-w-4xl mx-auto px-6 py-12">
        <header class="mb-10 text-center">
            @if($post->featured_image)
            <div class="mb-8 rounded-2xl overflow-hidden aspect-video">
                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            </div>
            @endif
            
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4">{{ $post->title }}</h1>
            
            <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
                <span>{{ $post->author->name }}</span>
                <span>•</span>
                <span>{{ $post->published_at ? $post->published_at->format($dateFormat) : 'Draft' }}</span>
            </div>
        </header>

        <article class="prose prose-lg dark:prose-invert max-w-none">
            {!! $post->content !!}
        </article>

        @if($enableComments)
        <hr class="my-12 border-gray-200 dark:border-gray-800">
        <section>
            <h3 class="text-2xl font-bold mb-6">Comments</h3>
            @if($closeCommentsDays > 0 && $post->published_at && $post->published_at->addDays($closeCommentsDays)->isPast())
                <div class="bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 p-4 rounded-lg">
                    Comments are closed for this post.
                </div>
            @else
                <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-xl text-center text-gray-500">
                    <p>Comments are enabled but the comment system is not yet implemented.</p>
                </div>
            @endif
        </section>
        @endif
    </div>
</body>
</html>
