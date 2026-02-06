@php
    $postsArchiveSlug = \Plugins\Posts\Models\Setting::get('archive_slug', 'blog');
@endphp
<nav class="navbar navbar-expand-lg fixed-top py-3">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('themes/iccom/assets/logo.png') }}" alt="iCCom Logo" height="80">
        </a>
        <!-- Toggler removed -->
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <div class="nav-pill-container d-flex align-items-center bg-white rounded-pill px-2 py-1 shadow-sm mt-3 mt-lg-0">
                <a class="nav-link px-4 py-2 {{ request()->is('/') ? 'active rounded-pill text-white' : 'text-dark' }}" href="{{ url('/') }}">Home</a>
                <a class="nav-link px-4 py-2 {{ request()->is('event*') ? 'active rounded-pill text-white' : 'text-dark' }}" href="{{ url('/event') }}">Events</a>
                <a class="nav-link px-4 py-2 {{ request()->is($postsArchiveSlug . '*') ? 'active rounded-pill text-white' : 'text-dark' }}" href="{{ url('/' . $postsArchiveSlug) }}">Blog</a>
            </div>

            <!-- Mobile CTA shows in menu -->
            <div class="d-lg-none mt-3">
                <a href="{{ url('/membership') }}" class="btn btn-primary btn-cta rounded-pill px-4 w-100">Become a Member</a>
            </div>
        </div>
        <div class="d-none d-lg-block">
            <a href="{{ url('/membership') }}" class="btn btn-primary btn-cta rounded-pill px-4">Become a Member</a>
        </div>
    </div>
</nav>
