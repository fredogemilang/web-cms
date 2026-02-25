@extends('iccom::layouts.app')

@section('title', $post->title . ' - iCCom')

@section('content')
    <!-- Article Header Section -->
    <header class="blog-detail-header py-5">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center mt-5" data-aos="fade-up">
                    @if($post->categories->count() > 0)
                    <div class="text-muted small fw-bold mb-3 text-uppercase tracking-wide mt-5">
                        {{ $post->categories->first()->name }}
                    </div>
                    @endif
                    <h1 class="display-4 fw-bold mb-4">{{ $post->title }}</h1>
                    <div class="d-flex align-items-center justify-content-center gap-3 text-muted mb-5">
                        <div class="d-flex align-items-center gap-2">
                             <!-- Author avatar -->
                            <div class="bg-secondary rounded-circle"
                                style="width: 40px; height: 40px; background-size: cover; background-image: url('{{ $post->author && $post->author->avatar ? asset('storage/' . $post->author->avatar) : 'https://dummyimage.com/100x100/ccc/fff' }}');">
                            </div>
                            <span class="fw-semibold text-dark">{{ $post->author ? $post->author->name : 'Admin' }}</span>
                        </div>
                        <span>&bull;</span>
                        <span>{{ $post->published_at ? $post->published_at->format($dateFormat ?? 'M d, Y') : $post->created_at->format($dateFormat ?? 'M d, Y') }}</span>
                        <span>&bull;</span>
                        <span>{{ ceil(str_word_count(strip_tags($post->content)) / 200) }} min read</span>
                    </div>
                </div>
                <div class="col-12 text-center" data-aos="fade-up" data-aos-delay="100">
                    @if($post->featured_image)
                        @php
                            $featuredImageUrl = $post->featured_image;
                            // Check if it's a relative path (not starting with http)
                            if (!str_starts_with($featuredImageUrl, 'http')) {
                                $featuredImageUrl = asset('storage/' . $post->featured_image);
                            }
                        @endphp
                        <img src="{{ $featuredImageUrl }}" alt="{{ $post->title }}" class="img-fluid rounded-4 shadow w-100">
                    @else
                        <img src="/storage/media/default-blog-iccomjpg-1770368714-AYUyHkb8.webp" alt="{{ $post->title }}" class="img-fluid rounded-4 shadow w-100">
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Content & TOC Section -->
    <section class="blog-content-section py-5">
        <div class="container">
            <div class="row g-5">
                <!-- Main Content -->
                <div class="col-lg-8" data-aos="fade-right">
                    <article class="blog-article-content">

                        <div class="post-content">
                            {!! $post->content !!}
                        </div>
                    </article>

                    <!-- Tags -->
                    @if($post->tags->count() > 0)
                    <div class="post-tags mt-5 pt-4 border-top">
                        <p class="fw-bold mb-3">Tags:</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($post->tags as $tag)
                            <a href="{{ route('posts.index') }}?tag={{ $tag->slug }}" class="badge bg-light text-dark text-decoration-none px-3 py-2 rounded-pill">{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Share Buttons -->
                    <div class="share-buttons mt-5 pt-4 border-top">
                        <p class="fw-bold mb-3">Share this article:</p>
                        <div class="d-flex gap-2">
                            @php
                                $shareUrl = urlencode(request()->url());
                                $shareTitle = urlencode($post->title);
                            @endphp
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" class="btn btn-outline-secondary rounded-circle p-2"
                                style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><svg
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M11.75 0h-2.5c-2.9 0-4.75 1.88-4.75 4.88V7H2v3h2.5v9h3.75v-9h2.5l.5-3h-3V4.75c0-.88.25-1.5 1.5-1.5h1.5V0Z" />
                                </svg></a>
                            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" class="btn btn-outline-secondary rounded-circle p-2"
                                style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><svg
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z" />
                                </svg></a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ $shareUrl }}&title={{ $shareTitle }}" target="_blank" class="btn btn-outline-secondary rounded-circle p-2"
                                style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><svg
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z" />
                                </svg></a>
                            <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" class="btn btn-outline-secondary rounded-circle p-2"
                                style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><svg
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z" />
                                </svg></a>
                        </div>
                    </div>
                </div>

                <!-- Sticky Sidebar (TOC) -->
                <div class="col-lg-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="toc-sidebar sticky-top" style="top: 120px; z-index: 10;">
                        <div class="p-4 bg-light rounded-4">
                            <h5 class="fw-bold mb-3">Table of Contents</h5>
                            <nav id="toc" class="toc-nav">
                                <ul class="list-unstyled mb-0" id="toc-list">
                                    <!-- TOC will be generated via JavaScript -->
                                </ul>
                            </nav>
                        </div>

                        <!-- Categories -->
                        @if($post->categories->count() > 0)
                        <div class="p-4 bg-light rounded-4 mt-4">
                            <h5 class="fw-bold mb-3">Categories</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($post->categories as $category)
                                <a href="{{ route('posts.index') }}?category={{ $category->slug }}" class="badge bg-primary text-white text-decoration-none px-3 py-2 rounded-pill">{{ $category->name }}</a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Articles Section -->
    @php
        $relatedPosts = \Plugins\Posts\Models\Post::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(3)
            ->get();
    @endphp
    @if($relatedPosts->count() > 0)
    <section class="related-articles-section py-5 bg-light">
        <div class="container">
            <h3 class="fw-bold mb-4" data-aos="fade-up">You Might Also Like</h3>
            <div class="row g-4" data-aos="fade-up" data-aos-delay="100">
                @foreach($relatedPosts as $relatedPost)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('posts.show', $relatedPost->slug) }}" class="text-decoration-none">
                        <div class="article-card h-100 d-flex flex-column bg-white rounded-4 overflow-hidden shadow-sm">
                            <div class="article-banner position-relative" style="height: 200px;">
                                @php
                                    $relatedImageUrl = $relatedPost->featured_image;
                                    if ($relatedImageUrl && !str_starts_with($relatedImageUrl, 'http')) {
                                        $relatedImageUrl = asset('storage/' . $relatedImageUrl);
                                    }
                                @endphp
                                <img src="{{ $relatedImageUrl ?: '/storage/media/default-blog-iccomjpg-1770368714-AYUyHkb8.webp' }}"
                                    class="img-fluid w-100 h-100 object-fit-cover" alt="{{ $relatedPost->title }}">
                            </div>
                            <div class="card-body p-4 flex-grow-1 d-flex flex-column justify-content-center">
                                <p class="small text-muted fw-semibold mb-2">{{ $relatedPost->author ? $relatedPost->author->name : 'Admin' }} | {{ $relatedPost->published_at ? $relatedPost->published_at->format('M d, Y') : $relatedPost->created_at->format('M d, Y') }}</p>
                                <h5 class="fw-bold mb-0 text-dark">{{ $relatedPost->title }}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Write Your Own Article CTA Section -->
    <section class="cta-write-article-section py-5">
        <div class="cta-background position-absolute top-0 start-0 w-100 h-100"
            style="background: url('{{ asset('themes/iccom/assets/bg-most-popular-article.jpg') }}') no-repeat center center; background-size: cover; z-index: -1;">
        </div>
        <div class="container py-5">
            <div class="cta-card bg-white rounded-4 shadow-lg overflow-hidden mx-auto" style="max-width: 1000px;" data-aos="zoom-in" data-aos-delay="100">
                <div class="row g-0">
                    <!-- Image Side -->
                    <div class="col-md-5 d-none d-md-block">
                        <img src="{{ asset('themes/iccom/assets/publish-front-hero.png') }}" alt="Write Article"
                            class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                    <!-- Content Side -->
                    <div class="col-md-7 d-flex align-items-center p-5 text-center text-md-center">
                        <div class="w-100">
                            <h2 class="fw-bold mb-3 text-dark">Write Your Own Article!</h2>
                            <p class="lead mb-4 text-dark" style="font-size: 1.1rem;">Be the Contributor of <span
                                    class="fw-bold">iCCom</span><br>by Publishing Your Article!</p>
                            <a href="{{ route('article-submission.form') }}"
                                class="btn btn-cta rounded-pill px-5 py-3 fw-bold shadow-sm"
                                style="font-size: 1.1rem;">Publish Your Article</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // Generate Table of Contents dynamically
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.querySelector('.post-content');
        const tocList = document.getElementById('toc-list');
        
        if (content && tocList) {
            const headings = content.querySelectorAll('h2, h3');
            
            if (headings.length > 0) {
                headings.forEach((heading, index) => {
                    // Add ID to heading if not present
                    if (!heading.id) {
                        heading.id = 'heading-' + index;
                    }
                    
                    const li = document.createElement('li');
                    li.classList.add('mb-2');
                    
                    const a = document.createElement('a');
                    a.href = '#' + heading.id;
                    a.classList.add('text-decoration-none', 'text-muted');
                    a.textContent = heading.textContent;
                    
                    // Add indentation for h3
                    if (heading.tagName === 'H3') {
                        li.classList.add('ps-3', 'small');
                    }
                    
                    li.appendChild(a);
                    tocList.appendChild(li);
                });
            } else {
                // Hide TOC if no headings
                tocList.closest('.toc-sidebar').querySelector('.p-4').style.display = 'none';
            }
        }
    });
</script>
@endpush
