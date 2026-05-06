@extends('iccom::layouts.app')

@section('title', 'Registration Successful - iCCom Indonesia Cloud Community')

@section('content')
    <div style="height: 100px;"></div> <!-- Spacer -->

    <!-- Success Hero Section -->
    <section class="success-section d-flex align-items-center position-relative text-white text-center">
        <div class="container position-relative z-2">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="zoom-in">
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5" class="text-white opacity-75">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h1 class="display-4 fw-bold mb-4">Thank you for registering!</h1>
                    <p class="lead mb-5 text-white-50">Your event registration has been submitted successfully.
                        We will send a confirmation to your email shortly.</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="{{ route('events.index') }}"
                            class="btn btn-cta btn-warning text-white rounded-pill px-5 py-3 fw-bold shadow-lg"
                            style="font-size: 1.1rem;">Back to Events</a>
                        <a href="{{ url('/') }}"
                            class="btn btn-outline-light rounded-pill px-5 py-3 fw-bold"
                            style="font-size: 1.1rem;">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
