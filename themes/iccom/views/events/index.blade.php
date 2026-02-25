@extends('iccom::layouts.app')

@section('title', 'Events - iCCom Indonesia Cloud Community')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section flex align-items-center position-relative">
        <div class="container pt-5 mt-5">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <h1 class="display-3 fw-bold mb-3">Events</h1>
                    <p class="lead text-muted mb-4">
                        Learn from cloud experts, discover what's new in tech, and pick up career tips you can put to
                        work right away.
                    </p>
                </div>
                <!-- Placeholder for Hero Image from design -->
                <div class="col-lg-7 text-center">
                    <img src="{{ asset('themes/iccom/assets/events-front-right-hero-img.png') }}" alt="Events Hero" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Event Section -->
    <section class="upcoming-event-section">
        <div class="container">
            <h2 class="fw-bold mb-5 text-white">Upcoming Event</h2>
            <div class="row">
                <div class="col-md-8 col-lg-6">
                    @if($upcoming)
                    <div class="upcoming-card h-100 position-relative">
                        <div class="upcoming-badge-event-category">
                            @if($upcoming->category && $upcoming->category->image)
                                <img src="{{ $upcoming->category->image->url }}" alt="{{ $upcoming->category->name }}">
                            @elseif($upcoming->category)
                                <span class="badge bg-primary">{{ $upcoming->category->name }}</span>
                            @endif
                        </div>
                        <div class="upcoming-card-img-wrapper" style="height: 300px; overflow: hidden;">
                            @if($upcoming->featuredImage)
                                <img src="{{ $upcoming->featuredImage->url }}" alt="{{ $upcoming->title }}" class="img-fluid w-100 h-100" style="object-fit: cover;">
                            @else
                                <img src="{{ asset('themes/iccom/assets/gen-ai-today.png') }}" alt="{{ $upcoming->title }}" class="img-fluid w-100 h-100" style="object-fit: cover;">
                            @endif
                        </div>
                        <div class="upcoming-card-body">
                            <span class="badge badge-{{ $upcoming->event_type == 'online' ? 'online' : 'offline' }} mb-3 px-3 py-2 rounded-pill text-capitalize">{{ $upcoming->event_type }}</span>
                            <h4 class="fw-bold mb-3"><a href="{{ route('events.show', $upcoming->slug) }}" class="text-decoration-none text-dark">{{ $upcoming->title }}</a></h4>
                            <div class="d-flex align-items-center text-muted mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg> {{ $upcoming->formatted_date_range }}
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                @if(!$upcoming->is_all_day && $upcoming->start_date)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg> {{ $upcoming->start_date->format('H:i') }} - {{ $upcoming->end_date ? $upcoming->end_date->format('H:i') . ' WIB' : 'Finish' }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info">No upcoming events scheduled at the moment. Stay tuned!</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery-section">
        <div class="container">
            <h2 class="fw-bold mb-4">See What We've Been Up To</h2>
            <div class="position-relative">
                <div class="swiper gallery-swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide gallery-slide">
                            <img src="https://dummyimage.com/600x400/ccc/fff&text=Event+1" alt="Event">
                        </div>
                        <div class="swiper-slide gallery-slide">
                            <img src="https://dummyimage.com/600x400/ccc/fff&text=Event+2" alt="Event">
                        </div>
                        <div class="swiper-slide gallery-slide">
                            <img src="https://dummyimage.com/600x400/ccc/fff&text=Event+3" alt="Event">
                        </div>
                        <div class="swiper-slide gallery-slide">
                            <img src="https://dummyimage.com/600x400/ccc/fff&text=Event+4" alt="Event">
                        </div>
                    </div>
                </div>
                <div class="gallery-prev swiper-button-prev start-0 ms-n5"></div>
                <div class="gallery-next swiper-button-next end-0 me-n5"></div>
            </div>
        </div>
    </section>

    <!-- Event Listing Section -->
    <section class="event-listing-section">
        <div class="container">
            <!-- Filter Nav -->
            <ul class="nav event-filter-nav justify-content-center justify-content-lg-start" id="eventFilters">
                <li class="nav-item">
                    <button class="event-filter-link active" onclick="filterEvents('all')">All</button>
                </li>
                <li class="nav-item">
                    <button class="event-filter-link" onclick="filterEvents('offline')">Offline</button>
                </li>
                <li class="nav-item">
                    <button class="event-filter-link" onclick="filterEvents('online')">Online</button>
                </li>
                <li class="nav-item">
                    <button class="event-filter-link" onclick="filterEvents('ic-talk')">iC-Talk</button>
                </li>
                <li class="nav-item">
                    <button class="event-filter-link" onclick="filterEvents('ic-connect')">iC-Connect</button>
                </li>
                <li class="nav-item">
                    <button class="event-filter-link" onclick="filterEvents('ic-class')">iC-Class</button>
                </li>
                <li class="nav-item">
                    <button class="event-filter-link" onclick="filterEvents('ic-meethub')">iC-MeetHub</button>
                </li>
            </ul>

            <!-- Event Cards Grid -->
            <div class="row g-4" id="eventsGrid">
                @forelse($events as $event)
                <div class="col-md-6 col-lg-4 event-item {{ $event->event_type }} {{ $event->category ? $event->category->slug : '' }}">
                    <div class="event-card h-100">
                        <div class="position-relative">
                            <div style="height: 240px; overflow: hidden;">
                                @if($event->featuredImage)
                                    <img src="{{ $event->featuredImage->url }}" class="card-img-top event-card-img-top w-100 h-100" style="object-fit: cover;" alt="{{ $event->title }}">
                                @else
                                    <img src="https://dummyimage.com/600x400/eee/333&text={{ urlencode($event->title) }}" class="card-img-top event-card-img-top w-100 h-100" style="object-fit: cover;" alt="{{ $event->title }}">
                                @endif
                            </div>
                            @if($event->category)
                            <span class="badge-event-category">
                                @if($event->category->image)
                                    <img src="{{ $event->category->image->url }}" height="20" alt="{{ $event->category->name }}">
                                @else
                                    <span class="badge bg-light text-dark">{{ $event->category->name }}</span>
                                @endif
                            </span>
                            @endif
                        </div>
                        <div class="p-4">
                            <span class="event-badge badge-{{ $event->event_type == 'online' ? 'online' : 'offline' }} text-capitalize">{{ $event->event_type }}</span>
                            <h5 class="fw-bold mb-3 mt-2"><a href="{{ route('events.show', $event->slug) }}" class="text-decoration-none text-dark">{{ $event->title }}</a></h5>
                            <div class="text-muted small mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg> {{ $event->formatted_date_range }}
                            </div>
                            @if(!$event->is_all_day && $event->start_date)
                            <div class="text-muted small mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg> {{ $event->start_date->format('H:i') }} - {{ $event->end_date ? $event->end_date->format('H:i') : 'Finish' }} WIB
                            </div>
                            @endif
                            @if($event->location)
                            <div class="text-muted small">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg> {{ $event->location }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No other events found.</p>
                </div>
                @endforelse
            </div>
            
            <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap event-pagination-container">
                <div class="text-muted small mb-3 mb-md-0">
                    Showing {{ $events->firstItem() ?? 0 }} to {{ $events->lastItem() ?? 0 }} of {{ $events->total() }} results
                </div>
                <div>
                    {{ $events->appends(request()->query())->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // Initialize Gallery Swiper
    new Swiper(".gallery-swiper", {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        navigation: {
            nextEl: ".gallery-next",
            prevEl: ".gallery-prev",
        },
        breakpoints: {
            640: { slidesPerView: 2 },
            992: { slidesPerView: 3, spaceBetween: 30 }
        }
    });

    // Event Filtering Logic
    function filterEvents(category) {
        // Active Tab
        document.querySelectorAll('.event-filter-link').forEach(btn => btn.classList.remove('active'));
        if(event.target) event.target.classList.add('active');

        console.log("Filter by:", category);

        const items = document.querySelectorAll('.event-item');
        items.forEach(item => {
            if (category === 'all') {
                item.style.setProperty('display', 'block', 'important');
            } else {
                if (item.classList.contains(category)) {
                     item.style.setProperty('display', 'block', 'important');
                } else {
                     item.style.setProperty('display', 'none', 'important');
                }
            }
        });
    }
</script>
@endpush
