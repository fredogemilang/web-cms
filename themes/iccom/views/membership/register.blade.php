@extends('iccom::layouts.app')

@section('title', 'Become a Member - iCCom Indonesia Cloud Community')

@section('content')
    <div style="height: 100px;"></div> <!-- Spacer logic from template .spacer css might need to be checked, using inline style for safety -->

    <!-- Form Section (Overlapping) -->
    <section class="membership-form-section pb-5 position-relative">
        <div class="container">
            <div class="text-white text-center">
                <h1 class="display-4 fw-bold mb-3">Join iCCom Membership</h1>
                <p class="lead mb-2 text-white-50">Sign up for free and take part in social and educational activities
                    designed with you in mind.</p>
                <p class="lead mb-2 text-white-50">Discover new insights at our events, learn from member-written
                    articles,
                    and connect with people who share the same passion for cloud.</p>
                <h2 class="display-5 fw-bold mt-4">Let's #UnitedatCloud</h2>
            </div>

            <div class="publish-form-card bg-white rounded-4 p-5 shadow-lg mx-auto mt-5">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
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
                            <button type="submit"
                                class="btn btn-cta btn-warning text-white fw-bold rounded-pill px-5 py-2 shadow">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
