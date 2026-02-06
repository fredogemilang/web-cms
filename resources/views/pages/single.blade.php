<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->getMetaTitle() }}</title>
    @if($page->getMetaDescription())
        <meta name="description" content="{{ $page->getMetaDescription() }}">
    @endif
    @if($page->seo['og_title'] ?? null)
        <meta property="og:title" content="{{ $page->seo['og_title'] }}">
    @endif
    @if($page->seo['og_description'] ?? null)
        <meta property="og:description" content="{{ $page->seo['og_description'] }}">
    @endif
    @if($page->seo['og_image'] ?? null)
        <meta property="og:image" content="{{ asset('storage/' . $page->seo['og_image']) }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-['Inter']">
    @isset($isPreview)
        <div class="bg-yellow-500 text-yellow-900 text-center py-2 text-sm font-medium">
            Preview Mode - This page is not published yet
        </div>
    @endisset

    <main class="container mx-auto px-4 py-12 max-w-4xl">
        {{-- Page Header --}}
        <header class="mb-12">
            @if($page->featured_image)
                <img src="{{ asset('storage/' . $page->featured_image) }}"
                    alt="{{ $page->title }}"
                    class="w-full h-64 object-cover rounded-2xl mb-8">
            @endif
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                {{ $page->title }}
            </h1>
        </header>

        {{-- Page Blocks --}}
        <div class="space-y-8">
            @foreach($blocks as $block)
                @if($block->is_active)
                    <div class="block-{{ $block->type }}" data-block-name="{{ $block->name }}">
                        @switch($block->type)
                            @case('text')
                                <p class="text-lg text-gray-700">{{ $block->value }}</p>
                                @break

                            @case('textarea')
                                <div class="prose prose-lg max-w-none">
                                    {!! nl2br(e($block->value)) !!}
                                </div>
                                @break

                            @case('wysiwyg')
                                <div class="prose prose-lg max-w-none">
                                    {!! $block->value !!}
                                </div>
                                @break

                            @case('number')
                                <span class="text-4xl font-bold text-primary">
                                    {{ $block->getOption('prefix') }}{{ $block->value }}{{ $block->getOption('suffix') }}
                                </span>
                                @break

                            @case('media')
                                @if($block->value)
                                    <img src="{{ asset('storage/' . $block->value) }}"
                                        alt="{{ $block->label }}"
                                        class="w-full rounded-xl">
                                @endif
                                @break

                            @case('gallery')
                                @php
                                    $images = $block->getDecodedValue() ?? [];
                                @endphp
                                @if(count($images) > 0)
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                        @foreach($images as $image)
                                            <img src="{{ asset('storage/' . $image) }}"
                                                alt="Gallery image"
                                                class="w-full h-48 object-cover rounded-lg">
                                        @endforeach
                                    </div>
                                @endif
                                @break

                            @case('date')
                                <time class="text-gray-600" datetime="{{ $block->value }}">
                                    {{ \Carbon\Carbon::parse($block->value)->format('F j, Y') }}
                                </time>
                                @break

                            @case('time')
                                <time class="text-gray-600">{{ $block->value }}</time>
                                @break

                            @case('datetime')
                                <time class="text-gray-600" datetime="{{ $block->value }}">
                                    {{ \Carbon\Carbon::parse($block->value)->format('F j, Y \a\t g:i A') }}
                                </time>
                                @break

                            @case('color')
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg border shadow-sm" style="background-color: {{ $block->value }}"></div>
                                    <span class="font-mono text-sm">{{ $block->value }}</span>
                                </div>
                                @break

                            @case('icon')
                                <span class="material-symbols-outlined text-4xl text-primary">{{ $block->value }}</span>
                                @break

                            @case('switcher')
                                @if($block->getDecodedValue())
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-sm font-medium">
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                        Disabled
                                    </span>
                                @endif
                                @break

                            @case('select')
                            @case('radio')
                                <span class="text-gray-700">{{ $block->value }}</span>
                                @break

                            @case('checkbox')
                                @php
                                    $values = $block->getDecodedValue() ?? [];
                                @endphp
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($values as $value)
                                        <li class="text-gray-700">{{ $value }}</li>
                                    @endforeach
                                </ul>
                                @break

                            @case('repeater')
                                <div class="space-y-4">
                                    @foreach($block->childBlocks as $childBlock)
                                        @if($childBlock->is_active)
                                            <div class="p-4 bg-gray-50 rounded-lg">
                                                <strong class="text-sm text-gray-500 block mb-1">{{ $childBlock->label }}</strong>
                                                <span class="text-gray-700">{{ $childBlock->value }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @break

                            @default
                                <div class="text-gray-500 italic">{{ $block->value }}</div>
                        @endswitch
                    </div>
                @endif
            @endforeach
        </div>
    </main>

    <footer class="bg-gray-100 py-8 mt-16">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            &copy; {{ date('Y') }} - Powered by CMS
        </div>
    </footer>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</body>
</html>
