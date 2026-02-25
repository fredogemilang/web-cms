@extends('iccom::layouts.app')

@section('title', 'iCCom - Indonesia Cloud Community')

@section('content')
    <div x-data="{ showModal: false }">
    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center position-relative">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0" data-aos="fade-right">
                    <h1 class="display-3 fw-bold mb-2">{{ $page->getBlockValue('hero_title', 'iCCom') }}</h1>
                    <h2 class="h3 fw-bold mb-4">{{ $page->getBlockValue('hero_subtitle', 'Indonesia Cloud Community') }}</h2>
                    <p class="lead text-muted mb-4">
                        {{ $page->getBlockValue('hero_description') }}
                    </p>
                    <a href="#membership-form" class="btn btn-primary btn-cta rounded-pill px-4 py-2">Become a Member</a>
                </div>
                <div class="col-lg-7 position-relative" data-aos="fade-left" data-aos-delay="200">
                    <img src="{{ asset('themes/iccom/assets/front-right-hero-img.png') }}" alt="Community Illustration" class="img-fluid hero-img">
                </div>
            </div>
        </div>
    </section>

    <!-- Who Are We Section -->
    <section class="who-we-are position-relative">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-4 mb-lg-0" data-aos="fade-right">
                    <img src="{{ asset('themes/iccom/assets/section-2-front-left-img.png') }}" alt="Who Are We" class="img-fluid">
                </div>
                <div class="col-lg-7 text-white" data-aos="fade-left" data-aos-delay="200">
                    <h2 class="fw-bold mb-4">{{ $page->getBlockValue('who_are_we_title', 'Who Are We?') }}</h2>
                    <div class="mb-3">
                        {!! $page->getBlockValue('who_are_we_description') !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Arrow Divider -->
        <div class="custom-shape-divider-bottom">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"
                preserveAspectRatio="none">
                <polygon points="0,0 1200,0 600,120" class="shape-fill"></polygon>
            </svg>
        </div>
    </section>

    <!-- Core Value Section -->
    <section class="core-values position-relative py-5">
        <div class="container mt-5 pt-5">
            <h2 class="fw-bold mb-5" data-aos="fade-up">{{ $page->getBlockValue('iccom_core_value_title', 'iCCom Core Value') }}</h2>
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                    @php
                        $coreValues = json_decode($page->getBlockValue('iccom_core_value_loop'), true) ?? [];
                    @endphp
                    @foreach($coreValues as $value)
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0 me-3">
                            <img src="{{ asset('storage/' . ($value['core_icon'] ?? '')) }}" alt="{{ $value['core_title'] ?? '' }}" width="64">
                        </div>
                        <div>
                            <h5 class="fw-bold">{{ $value['core_title'] ?? '' }}</h5>
                            <p class="text-muted">{{ $value['core_description'] ?? '' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <img src="{{ asset('themes/iccom/assets/iCCom-Core-Value-section-3-front-right-img.png') }}" alt="Core Values"
                        class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section text-white text-center py-5">
        <div class="container py-4" data-aos="zoom-in">
            <h2 class="display-4 fw-bold"><span class="counter" data-target="{{ $page->getBlockValue('counter_member', 50000) }}">0</span>+</h2>
            <h3 class="fw-bold">{{ $page->getBlockValue('counter_title', 'Have Joined to Be a Part of iCCom') }}</h3>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section py-5 position-relative">
        <div class="container py-5">
            <h2 class="text-center fw-bold mb-5" data-aos="fade-up">{{ $page->getBlockValue('testimonal_title') }}</h2>
            <!-- Testimonial Swiper -->
            <div class="position-relative px-4 px-md-5" data-aos="fade-up" data-aos-delay="100">
                <div class="swiper testimonial-swiper pb-5" style="padding: 20px;">
                    <div class="swiper-wrapper">
                        @forelse($testimonials as $testimonial)
                        <div class="swiper-slide h-auto">
                            <div class="testimonial-card text-center p-4 rounded shadow-sm bg-white h-100 border mx-2">
                                <div class="quote-icon mb-3"><span>"</span></div>
                                @if($testimonial->getMeta('photo'))
                                    <img src="{{ asset('storage/' . $testimonial->getMeta('photo')) }}" class="user-img-circle mx-auto mb-3" alt="{{ $testimonial->title }}">
                                @elseif($testimonial->featured_image)
                                    <img src="{{ asset('storage/' . $testimonial->featured_image) }}" class="user-img-circle mx-auto mb-3" alt="{{ $testimonial->title }}">
                                @else
                                    <img src="https://i.pravatar.cc/300?u={{ $testimonial->id }}" class="user-img-circle mx-auto mb-3" alt="{{ $testimonial->title }}">
                                @endif
                                <h5 class="fw-bold mb-0">{{ $testimonial->title }}</h5>
                                <small class="text-muted">{{ $testimonial->getMeta('position') ?? 'Member' }}</small>
                                <p class="mt-3 text-muted small">"{{ $testimonial->getMeta('review') ?? $testimonial->excerpt ?? Str::limit(strip_tags($testimonial->content), 100) }}"</p>
                            </div>
                        </div>
                        @empty
                        <!-- Fallback Static Items if no data -->
                         <div class="swiper-slide h-auto">
                            <div class="testimonial-card text-center p-4 rounded shadow-sm bg-white h-100 border mx-2">
                                <div class="quote-icon mb-3"><span>“</span></div>
                                <img src="https://i.pravatar.cc/300?img=33" class="user-img-circle mx-auto mb-3"
                                    alt="Budi Santoso">
                                <h5 class="fw-bold mb-0">Budi Santoso</h5>
                                <small class="text-muted">Cloud Architect</small>
                                <p class="mt-3 text-muted small">"Bergabung dengan iCCom membuka wawasan baru tentang
                                    teknologi cloud terkini. Komunitasnya sangat suportif!"</p>
                            </div>
                        </div>
                         <div class="swiper-slide h-auto">
                            <div class="testimonial-card text-center p-4 rounded shadow-sm bg-white h-100 border mx-2">
                                <div class="quote-icon mb-3"><span>“</span></div>
                                <img src="https://i.pravatar.cc/300?img=44" class="user-img-circle mx-auto mb-3"
                                    alt="Siti Rahma">
                                <h5 class="fw-bold mb-0">Siti Rahma</h5>
                                <small class="text-muted">DevOps Specialist</small>
                                <p class="mt-3 text-muted small">"Event-event yang diadakan sangat relevan dengan
                                    industri. Saya bertemu banyak mentor hebat di sini."</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
                <!-- Navigation Buttons -->
                <div class="swiper-button-prev testimonial-prev"></div>
                <div class="swiper-button-next testimonial-next"></div>
            </div>
        </div>
    </section>

    <!-- Grow Together Section -->
    <section class="grow-section text-white text-center py-5">
        <div class="container py-5" data-aos="fade-up">
            <h2 class="fw-bold mb-4">{{ $page->getBlockValue('talent_referral_title') }}</h2>
            <div class="row justify-content-center">
                <div class="col-md-8 lead">
                    {!! $page->getBlockValue('talent_referral_description') !!}
                    <button type="button" @click="showModal = true" class="btn btn-warning btn-cta rounded-pill px-5 py-2 fw-bold text-white shadow mt-4">Upload Your CV Here</button>
                </div>
            </div>
            
        </div>
    </section>

    <!-- Partners Section -->
    <section class="partners-section py-5">
        <div class="container py-5 text-center">
            <h2 class="fw-bold mb-5" data-aos="fade-up">{{ $page->getBlockValue('our_partners_title', 'Our Partners') }}</h2>

            <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3 px-md-4" data-aos="fade-up" data-aos-delay="100">
                <ul class="nav nav-pills custom-partner-tabs text-start" id="partnerTabs">
                    <li class="nav-item">
                        <button class="nav-link active partner-filter-tab" onclick="filterPartners('corporate')">Corporate<br>Partner</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link partner-filter-tab" onclick="filterPartners('university')">University<br>Partner</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link partner-filter-tab" onclick="filterPartners('community')">Community<br>Partner</button>
                    </li>
                </ul>

                <div class="d-flex gap-3 align-items-center justify-content-center justify-content-md-start wi-100">
                    <div class="partner-prev custom-nav-arrow d-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </div>
                    <div class="partner-next custom-nav-arrow d-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </div>
            </div>

            <!-- Carousels Container -->
            <div class="position-relative mb-5" style="min-height: 200px;" data-aos="fade-up" data-aos-delay="200">
                <!-- Main Partner Swiper (Content populated by JS) -->
                <div id="partnerSwiper" class="swiper partner-swiper">
                    <div class="swiper-wrapper" id="partnerSwiperWrapper">
                        <!-- Slides will be injected here -->
                    </div>
                </div>

                <div class="mt-5">
                    <a href="mailto:committee@idcloudcommunity.org" class="btn btn-warning btn-cta rounded-pill px-5 text-white fw-bold">Be Our Partner Today!</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Membership Form Section -->
    <section class="membership-section py-5 text-white position-relative" id="membership-form">
        <div class="container py-5 position-relative" style="z-index: 2;">
            <div class="text-center mb-5" data-aos="fade-down">
                <h2 class="fw-bold">Join iCCom Membership</h2>
                <p class="mb-2 text-white-50">Sign up for free and take part in social and educational activities
                    designed with you in mind. Discover new insights at
                    our events, learn from member-written articles, and connect with people who share the same passion
                    for cloud.</p>
                <h4 class="fw-bold">Let's #UnitedatCloud</h4>
            </div>

            <div class="card p-4 p-md-5 rounded-4 border-0 text-dark mx-auto shadow-lg" style="max-width: 1000px;" data-aos="fade-up" data-aos-delay="100">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('membership.store') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        <!-- Row 1 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Name: <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-flushed @error('name') is-invalid @enderror" placeholder="Name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Job Level:</label>
                            <select name="job_level" class="form-select form-control-flushed">
                                <option value="">Select Job Level</option>
                                <option value="Entry Level" {{ old('job_level') == 'Entry Level' ? 'selected' : '' }}>Entry Level</option>
                                <option value="Mid Level" {{ old('job_level') == 'Mid Level' ? 'selected' : '' }}>Mid Level</option>
                                <option value="Senior Level" {{ old('job_level') == 'Senior Level' ? 'selected' : '' }}>Senior Level</option>
                                <option value="Manager" {{ old('job_level') == 'Manager' ? 'selected' : '' }}>Manager</option>
                                <option value="Director" {{ old('job_level') == 'Director' ? 'selected' : '' }}>Director</option>
                                <option value="C-Level" {{ old('job_level') == 'C-Level' ? 'selected' : '' }}>C-Level</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Domicile:</label>
                            <select name="domicile" class="form-select form-control-flushed">
                                <option value="">Select Domicile</option>
                                <option value="Jakarta" {{ old('domicile') == 'Jakarta' ? 'selected' : '' }}>Jakarta</option>
                                <option value="Bogor" {{ old('domicile') == 'Bogor' ? 'selected' : '' }}>Bogor</option>
                                <option value="Depok" {{ old('domicile') == 'Depok' ? 'selected' : '' }}>Depok</option>
                                <option value="Tangerang" {{ old('domicile') == 'Tangerang' ? 'selected' : '' }}>Tangerang</option>
                                <option value="Bekasi" {{ old('domicile') == 'Bekasi' ? 'selected' : '' }}>Bekasi</option>
                                <option value="Bandung" {{ old('domicile') == 'Bandung' ? 'selected' : '' }}>Bandung</option>
                                <option value="Surabaya" {{ old('domicile') == 'Surabaya' ? 'selected' : '' }}>Surabaya</option>
                                <option value="Other" {{ old('domicile') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Row 2 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">E-mail: <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-flushed @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Job Title:</label>
                            <input type="text" name="job_title" class="form-control form-control-flushed" placeholder="e.g. Software Engineer" value="{{ old('job_title') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Linkedin Account:</label>
                            <input type="text" name="linkedin" class="form-control form-control-flushed" placeholder="linkedin.com/in/username" value="{{ old('linkedin') }}">
                        </div>

                        <!-- Row 3 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Phone Number:</label>
                            <input type="text" name="phone" class="form-control form-control-flushed" placeholder="08xxxxxxxxxx" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Institution/Company:</label>
                            <input type="text" name="institution" class="form-control form-control-flushed" placeholder="Institution/Company" value="{{ old('institution') }}">
                        </div>
                        <div class="col-md-4">
                            <!-- Empty Column -->
                        </div>

                        <!-- Row 4 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Highest Education Level:</label>
                            <select name="education_level" class="form-select form-control-flushed">
                                <option value="">Select Education Level</option>
                                <option value="SMA/SMK" {{ old('education_level') == 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                                <option value="D3" {{ old('education_level') == 'D3' ? 'selected' : '' }}>D3</option>
                                <option value="S1" {{ old('education_level') == 'S1' ? 'selected' : '' }}>S1</option>
                                <option value="S2" {{ old('education_level') == 'S2' ? 'selected' : '' }}>S2</option>
                                <option value="S3" {{ old('education_level') == 'S3' ? 'selected' : '' }}>S3</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Industry:</label>
                            <select name="industry" class="form-select form-control-flushed">
                                <option value="">Select Industry</option>
                                <option value="Technology" {{ old('industry') == 'Technology' ? 'selected' : '' }}>Technology</option>
                                <option value="Finance" {{ old('industry') == 'Finance' ? 'selected' : '' }}>Finance</option>
                                <option value="Healthcare" {{ old('industry') == 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                                <option value="Education" {{ old('industry') == 'Education' ? 'selected' : '' }}>Education</option>
                                <option value="Government" {{ old('industry') == 'Government' ? 'selected' : '' }}>Government</option>
                                <option value="Retail" {{ old('industry') == 'Retail' ? 'selected' : '' }}>Retail</option>
                                <option value="Manufacturing" {{ old('industry') == 'Manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                <option value="Consulting" {{ old('industry') == 'Consulting' ? 'selected' : '' }}>Consulting</option>
                                <option value="Other" {{ old('industry') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <!-- Empty Column -->
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12 text-center mt-5">
                            <button type="submit" class="btn btn-cta btn-warning text-white fw-bold rounded-pill px-5 py-2 shadow">
                                Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Modal (Moved to bottom of content to ensure proper stacking and scope) -->
    <div x-show="showModal" style="display: none; z-index: 1055;" class="custom-modal-overlay w-100 h-100" aria-modal="true" role="dialog">
        <div class="h-100 w-100 d-flex align-items-center justify-content-center">
            <!-- Backdrop -->
            <div class="position-absolute w-100 h-100" 
                 style="background-color: rgba(0,0,0,0.5); backdrop-filter: blur(5px);"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showModal = false"></div>
            
            <!-- Modal Content Wrapper -->
            <div class="position-relative" 
                 style="max-width: 1000px; width: 90%; z-index: 1060;"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <!-- Close Button (Outside) -->
                <button type="button" class="btn-close position-absolute" 
                        style="top: -15px; right: -10px; z-index: 1070; background-color: red; color: white; border-radius: 50%; opacity: 1; padding: 0.75rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" 
                        @click="showModal = false" aria-label="Close"></button>

                <!-- Modal Body -->
                <div class="bg-white text-dark rounded-4 shadow-lg p-4 p-md-5" 
                     style="max-height: 90vh; overflow-y: auto;">
                    
                    <div class="text-start">
                        @php
                            $talentForm = \App\Models\Form::where('slug', 'talent-referral')->first();
                        @endphp
                        
                        @if($talentForm)
                            <div class="custom-form-styles">
                                {!! $talentForm->renderForm() !!}
                            </div>
                        @else
                            <div class="alert alert-warning">Form 'talent-referral' not found. Please create a form with this slug.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Scoped styles for the modal form to match theme or ensure visibility */
        .custom-form-styles .form-group {
            margin-bottom: 3rem !important; /* mb-5 equivalent */
        }
        .custom-form-styles label {
            font-weight: bold;
            font-size: 0.875rem; /* small */
            margin-bottom: 0.5rem;
        }
        /* Mimic .form-control-flushed */
        .custom-form-styles .form-control, 
        .custom-form-styles .form-select {
            border: none;
            border-bottom: 1px solid #ddd;
            border-radius: 0;
            padding-left: 0;
            padding-right: 0;
            background-color: transparent;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .custom-form-styles .form-control:focus,
        .custom-form-styles .form-select:focus {
            box-shadow: none;
            border-bottom-color: #29abe2; /* Primary Blue from theme */
        }
        
        .custom-form-styles .btn-primary {
            background-color: #ffc107; /* Warning color from theme */
            border-color: #ffc107;
            color: #fff;
            font-weight: bold;
            width: 100%;
            border-radius: 50rem; /* pill */
            padding: 0.75rem 2rem;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important; /* shadow-sm/lg equiv */
            margin-top: 1rem;
        }
        .custom-form-styles .btn-primary:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            transform: translateY(-2px);
            transition: all 0.2s;
        }
        /* Use fixed positioning for the modal container to overlay everything */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
        }
    </style>
    </div>
@endsection

@push('scripts')
<script>
    // Include specific JS for Home if needed, e.g. the Partner Filter logic from index.html
    // I will copy the Partner Logic here since it's specific to this page.
    
    // Dynamic Partner Filter Logic
    const partnerData = {
        corporate: [],
        university: [],
        community: []
    };

    @if(isset($partners) && count($partners) > 0)
        @foreach($partners as $partner)
            @php
                // Try to guess the type/category field
                // Adjust 'partner_type' to the actual meta field name if different
                $pType = strtolower($partner->getMeta('partner_type') ?? $partner->getMeta('category') ?? $partner->getMeta('type') ?? 'corporate');
                
                $category = 'corporate';
                if (str_contains($pType, 'university')) {
                    $category = 'university';
                } elseif (str_contains($pType, 'community')) {
                    $category = 'community';
                }

                // Image handling
                $metaLogo = $partner->getMeta('logo');
                if ($metaLogo) {
                    $imgUrl = asset('storage/' . $metaLogo);
                } elseif ($partner->featured_image) {
                     $imgUrl = asset('storage/' . $partner->featured_image);
                } else {
                    $imgUrl = 'https://dummyimage.com/150x150/d9d9d9/555555.png?text=' . urlencode($partner->title);
                }
            @endphp
            partnerData['{{ $category }}'].push({
                name: "{!! addslashes($partner->title) !!}",
                img: "{!! $imgUrl !!}"
            });
        @endforeach
    @else
        // Fallback Static Data
        partnerData.corporate = [
            { name: 'Google', img: 'https://dummyimage.com/150x150/d9d9d9/555555.png?text=Google' },
            { name: 'Microsoft', img: 'https://dummyimage.com/150x150/d9d9d9/555555.png?text=Microsoft' },
            { name: 'Amazon', img: 'https://dummyimage.com/150x150/d9d9d9/555555.png?text=Amazon' },
            { name: 'IBM', img: 'https://dummyimage.com/150x150/d9d9d9/555555.png?text=IBM' },
            { name: 'Oracle', img: 'https://dummyimage.com/150x150/d9d9d9/555555.png?text=Oracle' },
            { name: 'Intel', img: 'https://dummyimage.com/150x150/d9d9d9/555555.png?text=Intel' },
            { name: 'Cisco', img: 'https://dummyimage.com/150x150/d9d9d9/555555.png?text=Cisco' },
        ];
        partnerData.university = [
            { name: 'UI', img: 'https://dummyimage.com/150x150/cfe2ff/084298.png?text=UI' },
            { name: 'ITB', img: 'https://dummyimage.com/150x150/cfe2ff/084298.png?text=ITB' },
            { name: 'UGM', img: 'https://dummyimage.com/150x150/cfe2ff/084298.png?text=UGM' },
            { name: 'ITS', img: 'https://dummyimage.com/150x150/cfe2ff/084298.png?text=ITS' },
            { name: 'Binus', img: 'https://dummyimage.com/150x150/cfe2ff/084298.png?text=Binus' },
        ];
        partnerData.community = [
            { name: 'Docker', img: 'https://dummyimage.com/150x150/fff3cd/664d03.png?text=Docker' },
            { name: 'K8s', img: 'https://dummyimage.com/150x150/fff3cd/664d03.png?text=K8s' },
            { name: 'AWS UG', img: 'https://dummyimage.com/150x150/fff3cd/664d03.png?text=AWS%20UG' },
            { name: 'Google Cloud', img: 'https://dummyimage.com/150x150/fff3cd/664d03.png?text=Google%20Cloud' },
            { name: 'PyID', img: 'https://dummyimage.com/150x150/fff3cd/664d03.png?text=PyID' },
        ];
    @endif

    let partnerSwiperInstance = null;

    function initPartnerSwiper() {
        if (partnerSwiperInstance) {
            partnerSwiperInstance.destroy(true, true);
            partnerSwiperInstance = null;
        }

        partnerSwiperInstance = new Swiper("#partnerSwiper", {
            slidesPerView: 2,
            spaceBetween: 20,
            loop: true,
            observer: true,
            observeParents: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: ".partner-next",
                prevEl: ".partner-prev",
            },
            breakpoints: {
                640: { slidesPerView: 3, spaceBetween: 20 },
                768: { slidesPerView: 4, spaceBetween: 30 },
                1024: { slidesPerView: 5, spaceBetween: 40 },
            },
        });
    }

    function filterPartners(category) {
        // Update Tabs
        const tabMap = { 'corporate': 0, 'university': 1, 'community': 2 };
        const tabs = document.querySelectorAll('.partner-filter-tab');
        tabs.forEach(t => t.classList.remove('active'));
        if (tabs[tabMap[category]]) tabs[tabMap[category]].classList.add('active');

        // Render Slides
        const wrapper = document.getElementById('partnerSwiperWrapper');
        if (wrapper) {
            let data = partnerData[category] || [];
            // Duplicate data to satisfy Swiper Loop requirements (needs > slidesPerView)
            while (data.length > 0 && data.length < 12) {
                data = [...data, ...data];
            }

            wrapper.innerHTML = data.map(item => `
                <div class="swiper-slide text-center">
                    <div class="col-12" style="padding: 10px;">
                        <img src="${item.img}" class="partner-circle" alt="${item.name}">
                    </div>
                </div>
            `).join('');

            // Re-init Swiper
            initPartnerSwiper();
        }
    }

    // Init Default
    document.addEventListener('DOMContentLoaded', () => {
        filterPartners('corporate');
        
        // Ensure counters animate if this page is loaded
        const counters = document.querySelectorAll('.counter');
        const speed = 200; 

        if(counters.length > 0) {
            // Observer logic is in app.blade.php layout? 
            // Better to copy the Counter logic here specifically or ensure the layout script covers it.
            // The layout script has "Main Script" section which copied standard behaviors.
            // Let's create specific observer here to be sure.
            
            const animateCounters = () => {
                counters.forEach(counter => {
                    const updateCount = () => {
                        const target = +counter.getAttribute('data-target');
                        const count = +counter.innerText.replace(/,/g, ''); 
                        const inc = target / speed;
                        if (count < target) {
                            counter.innerText = Math.ceil(count + inc).toLocaleString('en-US');
                            setTimeout(updateCount, 15);
                        } else {
                            counter.innerText = target.toLocaleString('en-US');
                        }
                    };
                    updateCount();
                });
            }

            let observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounters();
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            document.querySelectorAll('.stats-section').forEach(section => {
                observer.observe(section);
            });
        }
        
         // Testimonials Swiper
        new Swiper(".testimonial-swiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".testimonial-next",
                prevEl: ".testimonial-prev",
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
        });
    });
</script>
@endpush
