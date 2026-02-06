<div>
    <div class="container" id="article-list-anchor">
        <!-- Tabs / Filter -->
        <div class="article-filter-nav mb-5">
            <a href="#" wire:click.prevent="setCategory('')" class="article-filter-link {{ $category === '' ? 'active' : '' }}">All</a>
            @foreach($categories as $cat)
            <a href="#" wire:click.prevent="setCategory('{{ $cat->slug }}')" class="article-filter-link {{ $category === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
            @endforeach
        </div>

        <!-- Grid of Articles -->
        <div class="row g-4 position-relative">
            <!-- Loading Indicator -->
            <div wire:loading.flex class="position-absolute w-100 h-100 top-0 start-0 bg-white bg-opacity-75 justify-content-center align-items-center" style="z-index: 10;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            @forelse($posts as $post)
            <div class="col-md-6 col-lg-4" wire:key="post-{{ $post->id }}">
                <a href="{{ route('posts.show', $post->slug) }}" class="text-decoration-none h-100 d-block">
                    <div class="article-card h-100 d-flex flex-column bg-white rounded-4 overflow-hidden shadow-sm">
                        <div class="article-banner position-relative">
                            @php
                                $postImg = $post->featured_image;
                                if ($postImg && !str_starts_with($postImg, 'http')) {
                                    $postImg = asset('storage/' . $postImg);
                                }
                            @endphp
                            <img src="{{ $postImg ?: '/storage/media/default-blog-iccomjpg-1770368714-AYUyHkb8.webp' }}"
                                class="img-fluid w-100 h-100 object-fit-cover" alt="{{ $post->title }}">
                        </div>
                        <div class="card-body p-4 flex-grow-1 d-flex flex-column justify-content-center">
                            <p class="small text-muted fw-semibold mb-2">{{ $post->author ? $post->author->name : 'Admin' }} | {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ Str::limit($post->title, 50) }}</h4>
                            <p class="text-muted mt-2 mb-0 small">{{ Str::limit(strip_tags($post->content), 100) }}</p>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">No articles found.</h4>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-5 d-flex justify-content-center">
            {{ $posts->links('posts::pagination.custom') }}
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
           @this.on('scroll-to-top', () => {
                const element = document.getElementById('article-list-anchor');
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
           });
           
           @this.on('update-url', (event) => {
                history.pushState({}, '', event.url);
           });
        });
    </script>
</div>
