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

    <!-- Event Documentation Section (if available) -->
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

    <!-- Booking Section -->
    <section class="booking-section py-5 position-relative">
        <div class="custom-shape-divider-top-booking">
            <!-- Optional geometric pattern overlay or just use CSS background -->
        </div>
        <div class="container py-5">
            <h2 class="text-center text-white fw-bold mb-5" data-aos="fade-up">Book Your Ticket Here!</h2>

            <div class="card p-4 p-lg-5 rounded-4 border-0 shadow-lg mx-auto" style="max-width: 900px;" data-aos="fade-up" data-aos-delay="100">
                <form>
                    <div class="row g-4">
                        <!-- Name -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Name</label>
                            <input type="text" class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="Name">
                        </div>
                        <!-- Job Level -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Job Level</label>
                            <select class="form-select border-0 border-bottom rounded-0 px-0">
                                <option selected disabled>Job Level</option>
                                <option>Entry Level</option>
                                <option>Mid Level</option>
                                <option>Senior Level</option>
                            </select>
                        </div>
                        <!-- Domicile -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Domicile</label>
                            <select class="form-select border-0 border-bottom rounded-0 px-0">
                                <option selected disabled>Domicile</option>
                                <option>Jakarta</option>
                                <option>Bandung</option>
                                <option>Other</option>
                            </select>
                        </div>

                        <!-- Email -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="Email">
                        </div>
                        <!-- Job Title -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Job Title</label>
                            <select class="form-select border-0 border-bottom rounded-0 px-0">
                                <option selected disabled>Job Title</option>
                                <option>Developer</option>
                                <option>Manager</option>
                            </select>
                        </div>
                        <!-- LinkedIn -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">LinkedIn Account</label>
                            <input type="text" class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="LinkedIn Account">
                        </div>

                        <!-- Phone Number -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="Phone Number">
                        </div>
                        <!-- Institution -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Institution/company</label>
                            <input type="text" class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="Institution/Company">
                        </div>
                        <div class="col-md-4 d-none d-md-block"></div>

                        <!-- Highest Education -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Highest Education Level</label>
                            <select class="form-select border-0 border-bottom rounded-0 px-0">
                                <option selected disabled>Highest education level</option>
                                <option>Bachelor</option>
                            </select>
                        </div>
                        <!-- Industry -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Industry</label>
                            <select class="form-select border-0 border-bottom rounded-0 px-0">
                                <option selected disabled>Industry</option>
                                <option>Tech</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-none d-md-block"></div>

                        <div class="col-12 text-center mt-5">
                            <button type="submit"
                                class="btn btn-cta btn-warning text-white rounded-pill px-5 py-2 fw-bold shadow">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
