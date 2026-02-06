@extends('iccom::layouts.app')

@section('title', 'Article Submitted - iCCom Indonesia Cloud Community')

@section('content')
    <section class="py-5" style="min-height: 60vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="none" viewBox="0 0 24 24" stroke="#83BF6E" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="fw-bold mb-3">Thank You!</h2>
                        <p class="text-muted mb-4">Your article has been submitted successfully. Our team will review your submission and get back to you soon.</p>
                        <a href="{{ url('/') }}" class="btn btn-warning text-white fw-bold rounded-pill px-5 py-2">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
