@extends('iccom::layouts.app')

@section('title', 'Registration Successful - iCCom Indonesia Cloud Community')

@section('content')
    <div style="height: 100px;"></div> <!-- Spacer -->

    <!-- Success Hero Section -->
    <section class="success-section d-flex align-items-center position-relative text-white text-center">
        <div class="container position-relative z-2">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="zoom-in">
                    <h1 class="display-4 fw-bold mb-4">Thank you for registering!</h1>
                    <p class="lead mb-5 text-white-50">Your membership application has been submitted successfully.
                        Our team will review your application and notify you via email.</p>
                    <a href="{{ url('/') }}"
                        class="btn btn-cta btn-warning text-white rounded-pill px-5 py-3 fw-bold shadow-lg"
                        style="font-size: 1.1rem;">Back to Home</a>
                </div>
            </div>
        </div>
    </section>
@endsection
