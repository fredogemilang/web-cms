@extends('iccom::layouts.app')

@section('title', 'Publish Your Article - iCCom Indonesia Cloud Community')

@section('content')
    <!-- Page Hero -->
    <header class="hero-section d-flex align-items-center position-relative">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-3">Fill This Form<br>and be Contributor<br>of iCCom Articles!</h1>
                    <p class="lead mb-4 fw-normal">As an iCCom member, you can contribute to publish articles related to cloud technology.</p>
                    <p class="lead mb-4 fw-normal">Please fill in this form to submit your article.</p>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="{{ asset('themes/iccom/assets/publish-front-hero.png') }}" alt="Contributor Hero" class="img-fluid hero-illustration">
                </div>
            </div>
        </div>
    </header>

    <!-- Form Section -->
    <section class="publish-form-section py-5">
        <div class="container">
            <div class="publish-form-card bg-white rounded-4 p-5 shadow-lg mx-auto">
                @if(session('error'))
                <div class="alert alert-danger mb-4">{{ session('error') }}</div>
                @endif
                
                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <form action="{{ route('article-submission.submit') }}" method="POST" enctype="multipart/form-data">
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
                                <option value="">Job Level</option>
                                <option value="Entry Level" {{ old('job_level') == 'Entry Level' ? 'selected' : '' }}>Entry Level</option>
                                <option value="Mid Level" {{ old('job_level') == 'Mid Level' ? 'selected' : '' }}>Mid Level</option>
                                <option value="Senior Level" {{ old('job_level') == 'Senior Level' ? 'selected' : '' }}>Senior Level</option>
                                <option value="Manager" {{ old('job_level') == 'Manager' ? 'selected' : '' }}>Manager</option>
                                <option value="Director" {{ old('job_level') == 'Director' ? 'selected' : '' }}>Director</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Domicile:</label>
                            <select name="domicile" class="form-select form-control-flushed">
                                <option value="">Domicile</option>
                                <option value="Jakarta" {{ old('domicile') == 'Jakarta' ? 'selected' : '' }}>Jakarta</option>
                                <option value="Outside Jakarta" {{ old('domicile') == 'Outside Jakarta' ? 'selected' : '' }}>Outside Jakarta</option>
                            </select>
                        </div>

                        <!-- Row 2 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">E-mail: <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-flushed @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Job Title:</label>
                            <input type="text" name="job_title" class="form-control form-control-flushed" placeholder="Job Title" value="{{ old('job_title') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Linkedin Account:</label>
                            <input type="text" name="linkedin" class="form-control form-control-flushed" placeholder="Linkedin Account" value="{{ old('linkedin') }}">
                        </div>

                        <!-- Row 3 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Phone Number:</label>
                            <input type="text" name="phone" class="form-control form-control-flushed" placeholder="Phone Number" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Institution/Company:</label>
                            <input type="text" name="institution" class="form-control form-control-flushed" placeholder="Institution/Company" value="{{ old('institution') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Upload Your Article</label>
                            <div class="upload-btn-wrapper">
                                <button type="button"
                                    class="btn btn-outline-warning w-100 text-start d-flex justify-content-between align-items-center"
                                    onclick="document.getElementById('article_file').click()">
                                    <span class="small text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <span id="file-name">Upload PDF Format</span>
                                    </span>
                                </button>
                                <input type="file" name="article_file" id="article_file" accept=".pdf" class="d-none" onchange="document.getElementById('file-name').textContent = this.files[0] ? this.files[0].name : 'Upload PDF Format'" />
                            </div>
                        </div>

                        <!-- Row 4 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Highest Education Level:</label>
                            <select name="education_level" class="form-select form-control-flushed">
                                <option value="">Highest Education Level</option>
                                <option value="High School" {{ old('education_level') == 'High School' ? 'selected' : '' }}>High School</option>
                                <option value="Diploma" {{ old('education_level') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                <option value="Bachelor" {{ old('education_level') == 'Bachelor' ? 'selected' : '' }}>Bachelor</option>
                                <option value="Master" {{ old('education_level') == 'Master' ? 'selected' : '' }}>Master</option>
                                <option value="Doctorate" {{ old('education_level') == 'Doctorate' ? 'selected' : '' }}>Doctorate</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Industry:</label>
                            <select name="industry" class="form-select form-control-flushed">
                                <option value="">Industry</option>
                                <option value="Technology" {{ old('industry') == 'Technology' ? 'selected' : '' }}>Technology</option>
                                <option value="Finance" {{ old('industry') == 'Finance' ? 'selected' : '' }}>Finance</option>
                                <option value="Healthcare" {{ old('industry') == 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                                <option value="Education" {{ old('industry') == 'Education' ? 'selected' : '' }}>Education</option>
                                <option value="Manufacturing" {{ old('industry') == 'Manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                <option value="Other" {{ old('industry') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12 text-center mt-5">
                            <button type="submit" class="btn btn-cta btn-warning text-white fw-bold rounded-pill px-5 py-2 shadow">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Guidelines Section -->
    <section class="guidelines-section py-5">
        <div class="container">
            <h5 class="fw-bold mb-4">Blog Article Guidelines</h5>
            <ol class="guideline-list small text-muted">
                <li class="mb-2">The article should be original and have never been published on the internet before.</li>
                <li class="mb-2">There is no minimum length for any article. However, we recommend 750-1,500 words for a greater depth of the topic.</li>
                <li class="mb-2">The article must provide benefits or new knowledge to community members.</li>
                <li class="mb-2">The article must be well-written and grammatically correct (in English or Bahasa Indonesia).</li>
                <li class="mb-2">To be more approachable to the readers, we recommend writing an article in a friendly, smart-casual tonality.</li>
                <li class="mb-2">Hard selling or marketing of a specific product/brand is disallowed, unless the product is related to the topic being addressed in the article.</li>
                <li class="mb-2">No discrimination or offense against a specific product/brand.</li>
                <li class="mb-2">The community committee has the authority to choose which articles are published; and take note that not all submitted articles will be chosen.</li>
                <li class="mb-2">The community committee reserves the right to change some of the article's content (if required).</li>
                <li class="mb-2">The community committee will inform the members whether or not their article will be published.</li>
                <li class="mb-2">Please include a brief biography if you would want this to feature with your article (a close-up headshot of yourself is also acceptable).</li>
                <li class="mb-2">All articles contributed to the community will become our copyright. However, the article will be made available under the author's name.</li>
            </ol>
        </div>
    </section>
@endsection
