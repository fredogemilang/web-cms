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

                {{-- Global error / success alert --}}
                <div id="formAlert" class="alert d-none mb-4" role="alert"></div>

                <form id="eventRegistrationForm"
                      action="{{ route('events.register', $event->slug) }}"
                      method="POST"
                      novalidate>
                    @csrf
                    <div class="row g-4">
                        <!-- Name -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="reg_name"
                                class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="Name">
                            <div class="invalid-feedback" id="err_name"></div>
                        </div>
                        <!-- Job Level -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Job Level</label>
                            <select name="job_level" id="reg_job_level"
                                class="form-select border-0 border-bottom rounded-0 px-0">
                                <option value="" selected disabled>Job Level</option>
                                <option>Entry Level</option>
                                <option>Mid Level</option>
                                <option>Senior Level</option>
                                <option>Manager</option>
                                <option>Director / VP</option>
                                <option>C-Level</option>
                                <option>Student</option>
                                <option>Other</option>
                            </select>
                            <div class="invalid-feedback" id="err_job_level"></div>
                        </div>
                        <!-- Domicile -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Domicile</label>
                            <select name="domicile" id="reg_domicile"
                                class="form-select border-0 border-bottom rounded-0 px-0">
                                <option value="" selected disabled>Domicile</option>
                                <option>Jakarta</option>
                                <option>Bandung</option>
                                <option>Surabaya</option>
                                <option>Yogyakarta</option>
                                <option>Bali</option>
                                <option>Medan</option>
                                <option>Other</option>
                            </select>
                            <div class="invalid-feedback" id="err_domicile"></div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="reg_email"
                                class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="Email">
                            <div class="invalid-feedback" id="err_email"></div>
                        </div>
                        <!-- Job Title -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Job Title</label>
                            <select name="job_title" id="reg_job_title"
                                class="form-select border-0 border-bottom rounded-0 px-0">
                                <option value="" selected disabled>Job Title</option>
                                <option>Software Engineer</option>
                                <option>Cloud Engineer</option>
                                <option>DevOps Engineer</option>
                                <option>Data Engineer</option>
                                <option>Product Manager</option>
                                <option>IT Manager</option>
                                <option>Consultant</option>
                                <option>Architect</option>
                                <option>Student</option>
                                <option>Other</option>
                            </select>
                            <div class="invalid-feedback" id="err_job_title"></div>
                        </div>
                        <!-- LinkedIn -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">LinkedIn Account</label>
                            <input type="text" name="linkedin" id="reg_linkedin"
                                class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="LinkedIn Account">
                            <div class="invalid-feedback" id="err_linkedin"></div>
                        </div>

                        <!-- Phone Number -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" name="phone" id="reg_phone"
                                class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="Phone Number">
                            <div class="invalid-feedback" id="err_phone"></div>
                        </div>
                        <!-- Institution -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Institution/Company</label>
                            <input type="text" name="institution" id="reg_institution"
                                class="form-control border-0 border-bottom rounded-0 px-0"
                                placeholder="Institution/Company">
                            <div class="invalid-feedback" id="err_institution"></div>
                        </div>
                        <div class="col-md-4 d-none d-md-block"></div>

                        <!-- Highest Education -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Highest Education Level</label>
                            <select name="highest_education" id="reg_highest_education"
                                class="form-select border-0 border-bottom rounded-0 px-0">
                                <option value="" selected disabled>Highest education level</option>
                                <option>High School</option>
                                <option>Diploma (D3)</option>
                                <option>Bachelor (S1)</option>
                                <option>Master (S2)</option>
                                <option>Doctorate (S3)</option>
                                <option>Other</option>
                            </select>
                            <div class="invalid-feedback" id="err_highest_education"></div>
                        </div>
                        <!-- Industry -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Industry</label>
                            <select name="industry" id="reg_industry"
                                class="form-select border-0 border-bottom rounded-0 px-0">
                                <option value="" selected disabled>Industry</option>
                                <option>Technology</option>
                                <option>Finance / Banking</option>
                                <option>E-Commerce / Retail</option>
                                <option>Healthcare</option>
                                <option>Manufacturing</option>
                                <option>Government</option>
                                <option>Education</option>
                                <option>Telecommunications</option>
                                <option>Logistics</option>
                                <option>Other</option>
                            </select>
                            <div class="invalid-feedback" id="err_industry"></div>
                        </div>
                        <div class="col-md-4 d-none d-md-block"></div>

                        <div class="col-12 text-center mt-5">
                            <button type="submit" id="submitRegistrationBtn"
                                class="btn btn-cta btn-warning text-white rounded-pill px-5 py-2 fw-bold shadow">
                                <span id="submitBtnText">Submit</span>
                                <span id="submitBtnSpinner" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Submitting...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
    (function () {
        const form = document.getElementById('eventRegistrationForm');
        if (!form) return;

        const btn        = document.getElementById('submitRegistrationBtn');
        const btnText    = document.getElementById('submitBtnText');
        const btnSpinner = document.getElementById('submitBtnSpinner');
        const alert      = document.getElementById('formAlert');

        // Field → error div mapping
        const fields = ['name', 'email', 'phone', 'institution', 'job_level',
                        'domicile', 'job_title', 'linkedin', 'highest_education', 'industry'];

        function clearErrors() {
            fields.forEach(function (field) {
                const input = form.querySelector('[name="' + field + '"]');
                const errDiv = document.getElementById('err_' + field);
                if (input)  input.classList.remove('is-invalid');
                if (errDiv) errDiv.textContent = '';
            });
            alert.classList.add('d-none');
            alert.className = 'alert d-none mb-4';
            alert.textContent = '';
        }

        function showFieldErrors(errors) {
            Object.keys(errors).forEach(function (field) {
                const input  = form.querySelector('[name="' + field + '"]');
                const errDiv = document.getElementById('err_' + field);
                if (input)  input.classList.add('is-invalid');
                if (errDiv) errDiv.textContent = errors[field][0];
            });
            // Scroll to first error
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function showGlobalAlert(message, type) {
            alert.className = 'alert alert-' + type + ' mb-4';
            alert.textContent = message;
        }

        function setLoading(loading) {
            btn.disabled = loading;
            btnText.classList.toggle('d-none', loading);
            btnSpinner.classList.toggle('d-none', !loading);
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors();
            setLoading(true);

            const formData = new FormData(form);

            fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ?
                        document.querySelector('meta[name="csrf-token"]').getAttribute('content') :
                        formData.get('_token'),
                },
                body: formData,
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function (result) {
                const { status, data } = result;

                if (data.success && data.redirect) {
                    // Redirect to success page
                    window.location.href = data.redirect;
                    return;
                }

                setLoading(false);

                if (status === 422 && data.errors) {
                    showFieldErrors(data.errors);
                } else {
                    showGlobalAlert(data.message || 'An error occurred. Please try again.', 'danger');
                    window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 100, behavior: 'smooth' });
                }
            })
            .catch(function () {
                setLoading(false);
                showGlobalAlert('Network error. Please check your connection and try again.', 'danger');
            });
        });
    })();
    </script>
@endsection
