@extends('iccom::layouts.app')

@section('title', 'Blog - iCCom Indonesia Cloud Community')

@section('content')
    <!-- Page Hero -->
    <header class="hero-section d-flex align-items-center position-relative">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-3">Blog</h1>
                    <p class="lead mb-4">Share what you know, inspire others, and become a contributor to the iCCom
                        community!</p>
                    <a href="{{ route('article-submission.form') }}" class="btn btn-cta btn-warning text-white rounded-pill px-4 py-2 fw-bold shadow">Publish
                        Your Article</a>
                </div>
                <!-- Reuse Hero Image from Events or similar placeholder -->
                <div class="col-lg-8 text-center">
                    <img src="{{ asset('themes/iccom/assets/blog_front_hero.png') }}" alt="Blog Hero" class="img-fluid">
                </div>
            </div>
        </div>
    </header>

    <!-- Most Popular Article Section -->
    <section class="most-popular-section py-5 position-relative overflow-hidden">
        <!-- Background Overlay for Geometry -->
        <div class="background-geometry"></div>

        <div class="container position-relative z-1">
            <h2 class="fw-bold mb-4">Most Popular Article</h2>
            @if($featuredPosts->count() > 0)
            <div class="row g-4">
                <!-- Large Featured Article -->
                @php
                    $mainFeatured = $featuredPosts->first();
                    $sideFeatured = $featuredPosts->skip(1);
                @endphp
                <div class="col-lg-6">
                    <a href="{{ route('posts.show', $mainFeatured->slug) }}" class="text-decoration-none">
                        <div class="popular-card popular-card-large h-100 d-flex flex-column">
                            <!-- Banner Half -->
                            <div class="popular-banner-container overflow-hidden position-relative">
                                @php
                                    $mainImg = $mainFeatured->featured_image;
                                    if ($mainImg && !str_starts_with($mainImg, 'http')) {
                                        $mainImg = asset('storage/' . $mainImg);
                                    }
                                @endphp
                                <img src="{{ $mainImg ?: '/storage/media/default-blog-iccomjpg-1770368714-AYUyHkb8.webp' }}" alt="{{ $mainFeatured->title }}"
                                    class="img-fluid w-100 h-100 object-fit-cover">
                            </div>
                            <!-- Body Half -->
                            <div class="popular-body flex-grow-1 bg-white p-4">
                                <h6 class="text-muted mb-2 small fw-semibold">{{ $mainFeatured->author ? $mainFeatured->author->name : 'Admin' }} | {{ $mainFeatured->published_at ? $mainFeatured->published_at->format('M d, Y') : $mainFeatured->created_at->format('M d, Y') }}</h6>
                                <h2 class="fw-bold mb-0 display-6 text-dark" style="line-height: 1.2;">{{ $mainFeatured->title }}</h2>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Side Articles List -->
                <div class="col-lg-6">
                    <div class="d-flex flex-column gap-3 h-100">
                        @foreach($sideFeatured as $post)
                        <a href="{{ route('posts.show', $post->slug) }}" class="text-decoration-none">
                            <div class="popular-card popular-card-small box-shadow-sm">
                                <div class="popular-item d-flex h-100 align-items-stretch">
                                    <div class="popular-banner-small w-50 position-relative">
                                        @php
                                            $sideImg = $post->featured_image;
                                            if ($sideImg && !str_starts_with($sideImg, 'http')) {
                                                $sideImg = asset('storage/' . $sideImg);
                                            }
                                        @endphp
                                        <img src="{{ $sideImg ?: '/storage/media/default-blog-iccomjpg-1770368714-AYUyHkb8.webp' }}" alt="{{ $post->title }}"
                                            class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <div
                                        class="popular-body-small w-50 bg-white p-3 d-flex flex-column justify-content-center">
                                        <p class="text-muted mb-1 x-small fw-bold">{{ $post->author ? $post->author->name : 'Admin' }} | {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}</p>
                                        <h6 class="fw-bold mb-0 text-dark">{{ Str::limit($post->title, 60) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info">No featured posts found.</div>
            @endif
        </div>
    </section>

    <!-- Article Listing Section -->
    <!-- Article Listing Section -->
    <section class="article-listing-section py-5">
        @livewire('posts.blog-list', ['category' => $category ?? null])
    </section>
@endsection
