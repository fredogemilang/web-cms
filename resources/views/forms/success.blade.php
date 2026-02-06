@extends('iccom::layouts.app')

@section('title', 'Form Submitted - ' . $form->name)

@section('content')
    <div style="height: 100px;"></div> <!-- Spacer -->

    <!-- Success Hero Section -->
    <section class="success-section d-flex align-items-center position-relative text-white text-center">
        <div class="container position-relative z-2">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">{{ $title ?? 'Thank You!' }}</h1>
                    <p class="lead mb-5 text-white-50">{{ $message ?? 'Your submission has been received successfully.' }}</p>
                    <a href="{{ url('/') }}"
                        class="btn btn-cta btn-warning text-white rounded-pill px-5 py-3 fw-bold shadow-lg"
                        style="font-size: 1.1rem;">Back to Home</a>
                </div>
            </div>
        </div>
    </section>
@endsection
