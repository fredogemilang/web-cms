@extends('iccom::layouts.app')

@section('title', 'Become Our Partner - iCCom')

@section('content')
    <section class="hero-section d-flex align-items-center position-relative py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Partner with iCCom</h1>
                    <p class="lead mb-4">Join forces with Indonesia's largest cloud community. Lets collaborate to nurture talent and drive innovation.</p>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="{{ asset('themes/iccom/assets/strategic-alliance-icon-with-white-bg.png') }}" class="img-fluid" style="max-height: 300px;">
                </div>
            </div>
        </div>
    </section>

    <section class="partner-form-section py-5 bg-light">
        <div class="container">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden mx-auto" style="max-width: 800px;">
                <div class="card-header bg-primary text-white p-4 text-center">
                    <h3 class="fw-bold mb-0">Partnership Inquiry</h3>
                </div>
                <div class="card-body p-5">
                    <form>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Company / Organization Name</label>
                                <input type="text" class="form-control" placeholder="Your Company Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Website</label>
                                <input type="url" class="form-control" placeholder="https://">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Contact Person Name</label>
                                <input type="text" class="form-control" placeholder="Full Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control" placeholder="email@company.com">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Partnership Type</label>
                                <select class="form-select">
                                    <option selected disabled>Choose partnership type...</option>
                                    <option value="corporate">Corporate Partner (Sponsorship)</option>
                                    <option value="university">University Partner (Education)</option>
                                    <option value="community">Community Partner (Collaboration)</option>
                                    <option value="media">Media Partner</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Message / Proposal Summary</label>
                                <textarea class="form-control" rows="5" placeholder="Tell us how you'd like to partner with us..."></textarea>
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-warning btn-cta text-white rounded-pill px-5 py-3 fw-bold shadow">Submit Inquiry</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
