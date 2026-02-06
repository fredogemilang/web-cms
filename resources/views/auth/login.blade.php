<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Login - Welcome Back</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <!-- Initialize dark mode before page renders to prevent flash -->
    <script>
        (function() {
            function applyTheme() {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
            applyTheme();
        })();
    </script>
</head>
<body class="login-body text-[#111827] dark:text-[#FCFCFC] transition-colors duration-200 antialiased min-h-screen flex items-center justify-center p-4">
    <!-- Theme Toggle -->
    <div class="fixed top-8 right-8" x-data="{ 
        darkMode: document.documentElement.classList.contains('dark'),
        toggle() {
            this.darkMode = !this.darkMode;
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        }
    }">
        <button 
            @click="toggle()"
            class="flex h-12 w-12 items-center justify-center rounded-full bg-white dark:bg-[#272B30] text-[#6F767E] dark:text-[#FCFCFC] shadow-lg hover:bg-gray-50 dark:hover:bg-[#3a3f47] transition-all focus:outline-none border border-gray-200 dark:border-white/5">
            <span class="material-symbols-outlined text-[24px]" x-show="!darkMode" x-cloak>dark_mode</span>
            <span class="material-symbols-outlined text-[24px]" x-show="darkMode" x-cloak>light_mode</span>
        </button>
    </div>

    <div class="w-full max-w-[440px]">
        <!-- Header Logo -->
        <div class="flex flex-col items-center mb-10">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white dark:bg-[#272B30] text-[#2563EB] dark:text-white mb-6 shadow-xl border border-gray-200 dark:border-white/5">
                <span class="material-symbols-outlined text-3xl">grid_view</span>
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-[#111827] dark:text-white mb-2">Welcome back</h1>
            <p class="text-[#6F767E] font-medium">Please enter your details to sign in.</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl p-8 md:p-10 border border-gray-200 dark:border-[#272B30] shadow-2xl">
            <!-- SSO Buttons -->
            <div class="space-y-3 mb-8">
                <button type="button" class="sso-button">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"></path>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                    </svg>
                    <span class="dark:text-white">Sign in with Google</span>
                </button>
                <button type="button" class="sso-button">
                    <svg class="w-5 h-5" viewBox="0 0 23 23">
                        <path d="M0 0h11v11H0zM12 0h11v11H12zM0 12h11v11H0zM12 12h11v11H12z" class="fill-[#333] dark:fill-[#f3f3f3]"></path>
                    </svg>
                    <span class="dark:text-white">Sign in with Microsoft</span>
                </button>
            </div>

            <!-- Divider -->
            <div class="relative mb-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-[#272B30]"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-white dark:bg-[#1A1A1A] px-4 text-[#6F767E] font-bold tracking-widest">or</span>
                </div>
            </div>

            <!-- Alert Messages -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 text-green-600 dark:text-green-400 rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-xl">check_circle</span>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-xl">error</span>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 rounded-xl">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-xl">error</span>
                        <span class="text-sm font-bold">There were errors with your submission</span>
                    </div>
                    <ul class="list-disc list-inside text-sm space-y-1 ml-8">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Field -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2" for="email">
                        Email Address
                    </label>
                    <input 
                        class="input-field @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                        id="email" 
                        name="email"
                        type="email" 
                        value="{{ old('email') }}"
                        placeholder="name@company.com"
                        required
                        autofocus
                    >
                </div>

                <!-- Password Field -->
                <div x-data="{ showPassword: false }">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider" for="password">
                            Password
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-xs font-bold text-[#2563EB] hover:underline transition-all" href="{{ route('password.request') }}">
                                Forgot Password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <input 
                            class="input-field pr-12 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                            id="password" 
                            name="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                            required
                        >
                        <button 
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-[#6F767E] hover:text-[#111827] dark:hover:text-white transition-colors focus:outline-none"
                        >
                            <span class="material-symbols-outlined text-xl" x-show="!showPassword" x-cloak>visibility</span>
                            <span class="material-symbols-outlined text-xl" x-show="showPassword" x-cloak>visibility_off</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember"
                        class="w-4 h-4 bg-white dark:bg-[#0B0B0B] border-gray-300 dark:border-[#272B30] rounded text-[#2563EB] focus:ring-[#2563EB] focus:ring-offset-0 focus:ring-offset-white dark:focus:ring-offset-[#1A1A1A]"
                    >
                    <label for="remember" class="ml-3 text-sm text-[#6F767E] font-medium">Remember me</label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full bg-[#2563EB] hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-[#2563EB]/20 mt-4 active:scale-[0.98]"
                >
                    Sign In
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-center text-sm text-[#6F767E] font-medium">
            Don't have an account?
            <a class="text-[#111827] dark:text-white hover:underline font-bold" href="#">Contact your Administrator</a>
        </p>

        <!-- Copyright -->
        <p class="mt-4 text-center text-xs text-[#6F767E]/60">
            &copy; {{ date('Y') }} CMS Panel. All rights reserved.
        </p>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
