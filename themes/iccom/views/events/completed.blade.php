@extends('iccom::layouts.app')

@section('title', 'Event Details - iCCom Indonesia Cloud Community')

@section('content')
    <!-- Event Detail Section -->
    <section class="event-detail-section pt-5 mt-5">
        <div class="container pt-4">
            <!-- Banner -->
            <div class="event-banner mb-5 position-relative" data-aos="fade-up">
                <!-- Add badge logo if needed, similar to events page -->
                @if($event->category && $event->category->image)
                <div class="upcoming-badge-event-category" style="top: 30px; right: 0;">
                     <img src="/storage/{{ $event->category->image->path }}" alt="{{ $event->category->name }}">
                </div>
                @endif
                
                @if($event->featuredImage)
                    <img src="/storage/{{ $event->featuredImage->path }}" alt="{{ $event->title }}" class="img-fluid w-100 rounded-4 shadow-sm">
                @else
                    <div class="w-100 rounded-4 bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 400px;">
                        No Image Available
                    </div>
                @endif
            </div>

            <!-- Title & Info -->
            <div class="mb-5" data-aos="fade-up" data-aos-delay="100">
                <span class="badge badge-online mb-3 px-3 py-2 rounded-pill text-capitalize">{{ $event->event_type }}</span>
                <h1 class="fw-bold mb-3">{{ $event->title }}</h1>

                <div class="d-flex flex-column gap-2 text-muted mb-4">
                    @if($event->start_date)
                    <div class="d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2" class="me-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg> {{ $event->start_date->isoFormat('D MMMM YYYY') }}
                    </div>
                    <div class="d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2" class="me-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg> {{ $event->start_date->format('H:i') }} - {{ $event->end_date ? $event->end_date->format('H:i') : 'End' }} {{ $event->timezone ? "($event->timezone)" : '' }}
                    </div>
                    @endif
                    
                    @if($event->location)
                    <div class="d-flex align-items-center">
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                         {{ $event->location }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Speakers -->
            @if($event->speakers && $event->speakers->count() > 0)
            <div class="mb-5" data-aos="fade-up" data-aos-delay="200">
                <h4 class="fw-bold mb-4">Speakers</h4>
                <div class="row align-items-center">
                    @foreach($event->speakers as $speaker)
                    <div class="col-md-3 text-center mb-4">
                        @if($speaker->photo)
                            <img src="/storage/{{ $speaker->photo->path }}" alt="{{ $speaker->name }}" class="speaker-avatar mx-auto mb-3" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($speaker->name) }}&background=random" alt="{{ $speaker->name }}" class="speaker-avatar mx-auto mb-3" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                        @endif
                        <h6 class="fw-bold mb-0">{{ $speaker->name }}</h6>
                        <small class="text-muted">{{ $speaker->title }} {{ $speaker->company ? '@ ' . $speaker->company : '' }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Event Details Text -->
            <div class="mb-5" data-aos="fade-up" data-aos-delay="300">
                <h4 class="fw-bold mb-3">Event Detail</h4>
                <div class="text-muted event-content">
                    {!! $event->content !!}
                </div>
            </div>
        </div>
    </section>

    <!-- Event Documentation Section -->
    <!-- Event Documentation Section -->
    @if(!empty($event->gallery_images) && count($event->gallery_images) > 0)
    <section class="event-documentation-section py-5" x-data="{ shownCount: 3, total: {{ count($event->gallery_images) }} }">
        <div class="container">
            <h4 class="fw-bold mb-4" data-aos="fade-up">Event Documentation</h4>

            <div class="row g-4 mb-5" data-aos="fade-up" data-aos-delay="100">
                @foreach($event->gallery_images as $index => $image)
                <div class="col-md-4" x-show="{{ $index }} < shownCount" x-transition.opacity>
                    <img src="/storage/{{ $image }}" alt="{{ $event->title }} Documentation {{ $index + 1 }}"
                        class="img-fluid rounded-4 shadow-sm w-100 object-fit-cover" style="height: 250px;">
                </div>
                @endforeach
            </div>

            <div class="text-center" x-show="shownCount < total">
                <button type="button" @click="shownCount += 3" class="btn btn-primary rounded-pill px-5 py-2 fw-bold">See More</button>
            </div>
        </div>
    </section>
    @endif
@endsection
