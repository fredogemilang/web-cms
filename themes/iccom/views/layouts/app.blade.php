<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'iCCom - Indonesia Cloud Community')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('themes/iccom/assets/style.css') }}">
    @livewireStyles
    @stack('styles')
</head>
<body x-data="stickyNav()" @scroll.window="handleScroll()">

    @include('iccom::components.social-sidebar')
    @include('iccom::components.navigation')

    <main>
        @yield('content')
    </main>

    @include('iccom::components.footer')
    @include('iccom::components.mobile-nav')

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @livewireScripts
    
    <!-- Sticky Nav Alpine Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('stickyNav', () => ({
                lastScrollTop: 0,
                handleScroll() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const isMobile = window.innerWidth < 992;
                    const navbar = document.querySelector('.navbar');
                    const bottomNav = document.querySelector('.mobile-bottom-nav');

                    if (!isMobile) {
                        if (scrollTop > 10) {
                            if(navbar) navbar.classList.add('scrolled');
                        } else {
                            if(navbar) navbar.classList.remove('scrolled');
                        }

                        if (scrollTop > this.lastScrollTop && scrollTop > 100) {
                            if(navbar) navbar.classList.add('navbar-hidden');
                        } else {
                            if(navbar) navbar.classList.remove('navbar-hidden');
                        }
                    } else {
                        if(navbar) navbar.classList.remove('scrolled', 'navbar-hidden');
                        if (bottomNav) {
                            if (scrollTop > this.lastScrollTop && scrollTop > 50) {
                                bottomNav.classList.add('nav-hidden');
                            } else {
                                bottomNav.classList.remove('nav-hidden');
                            }
                        }
                    }

                    this.lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
                }
            }));
        });
    </script>

    <!-- AOS JS and Init -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select common elements within the main container
            const animatedElements = document.querySelectorAll('main h1, main h2, main h3, main p, main img, main .card, main .btn, main section > div');
            
            // Loop and add the data-aos attribute if not already present
            animatedElements.forEach((el) => {
                if (!el.hasAttribute('data-aos')) {
                    el.setAttribute('data-aos', 'fade-up');
                }
            });

            // Initialize AOS
            AOS.init({
                duration: 800, // values from 0 to 3000, with step 50ms
                easing: 'ease-out-cubic', // default easing for AOS animations
                once: true, // whether animation should happen only once - while scrolling down
                offset: 50, // offset (in px) from the original trigger point
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
