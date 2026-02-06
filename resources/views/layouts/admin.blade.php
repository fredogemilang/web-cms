<!DOCTYPE html>
<html lang="id" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - CMS Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .submenu-container {
            position: relative;
            transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }
        .submenu-list {
            position: relative;
            margin-left: 28px;
            padding-left: 0;
        }
        .submenu-list li {
            position: relative;
        }
        .submenu-list li::before {
            content: "";
            position: absolute;
            left: -1px;
            top: 0;
            bottom: -4px;
            border-left: 1px solid #b4b4b4;
        }
        .dark .submenu-list li::before {
            border-left: 1px solid #272B30;
        }
        .submenu-list li:last-child::before {
            display: none;
        }
        .submenu-item-connector {
            position: absolute;
            left: -1px;
            top: 0;
            height: 24px;
            width: 16px;
            border-left: 1px solid #b4b4b4;
            border-bottom: 1px solid #b4b4b4;
            border-bottom-left-radius: 8px;
            pointer-events: none;
        }
        .dark .submenu-item-connector {
            border-left: 1px solid #272B30;
            border-bottom: 1px solid #272B30;
        }
        aside {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-text {
            transition: opacity 0.2s ease-in-out, width 0.2s ease-in-out, margin 0.2s ease-in-out;
            white-space: nowrap;
            overflow: hidden;
        }
        aside.collapsed {
            width: 96px !important;
        }
        aside.collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            margin: 0;
            pointer-events: none;
            display: none;
        }
        aside.collapsed .submenu-container {
            display: none !important;
        }
        aside.collapsed .expand-icon {
            display: none;
        }
        aside.collapsed .nav-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        aside.collapsed .logo-container span:not(:first-child) {
            display: none;
        }
        /* Admin gradient backgrounds */
        .admin-body {
            background: radial-gradient(ellipse at top, #ffffff 0%, #F4F5F6 50%, #E8EAED 100%);
        }
        .dark .admin-body,
        .admin-body.dark-mode {
            background: radial-gradient(circle at center, #1A1A1A 0%, #0B0B0B 100%);
        }
        html.dark .admin-body {
            background: radial-gradient(circle at center, #1A1A1A 0%, #0B0B0B 100%);
        }
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
            
            // Apply on initial load
            applyTheme();

            // Apply on Livewire navigation
            document.addEventListener('livewire:navigated', applyTheme);
        })();
    </script>
    @livewireStyles
    @stack('styles')
</head>
<body class="admin-body text-[#111827] dark:text-[#FCFCFC] transition-colors duration-200 antialiased h-screen overflow-hidden">
    <!-- Global Livewire Loading Bar -->
    <div wire:loading class="fixed top-0 left-0 right-0 h-1 z-[100] overflow-hidden">
        <div class="h-full bg-[#2563EB] animate-indeterminate origin-left shadow-[0_0_10px_#2563EB]"></div>
    </div>

    <div class="flex h-full" x-data="sidebarController()">
        <!-- Sidebar -->
        <aside 
            :class="{ 'collapsed': sidebarCollapsed }"
            class="hidden w-[256px] flex-col bg-transparent md:flex shrink-0 border-r border-gray-200/50 dark:border-[#272B30]/50 relative transition-all duration-300"
            id="sidebar">
            
            <!-- Logo -->
            <div class="flex items-center gap-3 px-8 py-8 logo-container transition-all duration-300" style="min-height: 104px;">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-[#2563EB] shadow-sm ring-1 ring-gray-950/5 dark:bg-[#272B30] dark:text-white dark:ring-0">
                    <span class="material-symbols-outlined text-2xl">grid_view</span>
                </div>
                <span class="sidebar-text font-bold text-lg">CMS Panel</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 no-scrollbar">
                @include('components.admin.sidebar-new')
            </nav>
        </aside>

        <!-- Main Content -->
        <main 
            class="flex-1 overflow-y-auto bg-transparent scroll-smooth relative no-scrollbar"
            x-data="{ scrolled: false }"
            @scroll="scrolled = $el.scrollTop > 20">
            
            @sectionMissing('hide-header')
            <!-- Header -->
            <header 
                :class="{ 'bg-white/80 dark:bg-[#0B0B0B]/80 backdrop-blur-md border-b border-gray-200/50 dark:border-[#272B30]/50 shadow-sm': scrolled, 'bg-transparent border-b border-transparent': !scrolled }"
                class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 transition-all duration-300">
                <div class="flex items-center gap-4">
                    <button 
                        @click="toggleSidebar()"
                        class="p-2 rounded-xl hover:bg-gray-200 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:text-[#FCFCFC] transition-colors focus:outline-none">
                        <span class="material-symbols-outlined text-2xl" x-text="sidebarCollapsed ? 'menu' : 'menu_open'"></span>
                    </button>
                    <h1 class="text-4xl font-bold tracking-tight text-[#111827] dark:text-[#FCFCFC]">
                        @yield('page-title', 'Dashboard')
                    </h1>
                </div>
                <div class="flex items-center gap-6">
                    @hasSection('header-actions')
                        @yield('header-actions')
                    @else
                        <!-- Search -->
                        <div class="relative hidden md:block">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#6F767E]">
                                <span class="material-symbols-outlined text-[24px]">search</span>
                            </span>
                            <input
                                class="w-80 rounded-xl border border-gray-300 bg-white py-3 pl-12 pr-4 text-sm font-medium text-[#111827] placeholder-[#6F767E] shadow-sm ring-1 ring-gray-300 focus:ring-2 focus:ring-primary dark:bg-[#1A1D1F] dark:text-[#FCFCFC] dark:border-[#272B30] dark:ring-0 dark:focus:ring-white/20 transition-all"
                                placeholder="Search anything..." type="text">
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Theme Toggle -->
                            <button 
                                x-data="{ 
                                    darkMode: document.documentElement.classList.contains('dark'),
                                    toggle() {
                                        this.darkMode = !this.darkMode;
                                        document.documentElement.classList.toggle('dark');
                                        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                                    }
                                }"
                                @click="toggle()"
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-[#6F767E] shadow-sm hover:bg-gray-50 hover:text-[#111827] dark:bg-[#272B30] dark:text-[#FCFCFC] transition-colors focus:outline-none">
                                <span class="material-symbols-outlined text-[24px]" x-show="!darkMode" x-cloak>dark_mode</span>
                                <span class="material-symbols-outlined text-[24px]" x-show="darkMode" x-cloak>light_mode</span>
                            </button>
                            <!-- Notifications -->
                            <button class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-[#6F767E] shadow-sm hover:bg-gray-50 hover:text-[#111827] dark:bg-[#272B30] dark:text-[#6F767E] dark:hover:text-[#FCFCFC] transition-colors relative">
                                <span class="material-symbols-outlined text-[24px]">notifications</span>
                                <span class="absolute top-3 right-3 h-2 w-2 rounded-full bg-[#FF6A55] ring-2 ring-white dark:ring-[#0B0B0B]"></span>
                            </button>
                            <!-- User Avatar -->
                            <div x-data="{ userMenuOpen: false }" class="relative">
                                <div 
                                    @click="userMenuOpen = !userMenuOpen"
                                    class="h-12 w-12 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center cursor-pointer border-2 border-transparent hover:border-primary transition-all overflow-hidden">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-white font-bold">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                    @endif
                                </div>
                                <!-- Dropdown -->
                                <div 
                                    x-show="userMenuOpen"
                                    @click.away="userMenuOpen = false"
                                    x-transition
                                    x-cloak
                                    class="absolute right-0 mt-2 w-56 bg-white dark:bg-[#1A1A1A] rounded-xl shadow-xl py-2 z-50 border border-gray-200 dark:border-[#272B30]">
                                    <div class="px-4 py-3 border-b border-gray-100 dark:border-[#272B30]">
                                        <p class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-[#6F767E]">{{ auth()->user()->email }}</p>
                                    </div>
                                    <a href="#" class="block px-4 py-2 text-sm text-[#6F767E] hover:bg-gray-50 dark:hover:bg-[#272B30]/50 transition">Profile</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-[#6F767E] hover:bg-gray-50 dark:hover:bg-[#272B30]/50 transition">Settings</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-gray-50 dark:hover:bg-[#272B30]/50 transition font-medium">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </header>
            @endif

            <!-- Page Content -->
            <div class="@hasSection('hide-header') h-full @else px-6 pb-6 md:px-10 md:pb-10 @endif">
                <!-- Alert Messages -->
                <!-- Alert Messages Removed (Using Toasts) -->

                @yield('content')
            </div>
        </main>
    </div>

    <div 
        x-data="{ 
            notifications: [],
            add(type, message) {
                const id = Date.now();
                this.notifications.push({ id, type, message });
                setTimeout(() => {
                    this.remove(id);
                }, 3000);
            },
            remove(id) {
                this.notifications = this.notifications.filter(n => n.id !== id);
            },
            handleNotify(event) {
                const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                if (data) {
                    this.add(data.type, data.message);
                }
            }
        }"
        @notify.window="handleNotify($event)"
        class="fixed bottom-5 right-5 z-[90] flex flex-col gap-3 pointer-events-none"
    >
        <template x-for="notification in notifications" :key="notification.id">
            <div 
                x-transition:enter="transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-500 transform"
                x-transition:enter-start="translate-x-full opacity-0 scale-95"
                x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                x-transition:leave="transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-300 transform"
                x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                x-transition:leave-end="translate-x-full opacity-0 scale-95"
                class="flex items-center gap-3 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-xl px-4 py-3 shadow-2xl min-w-[300px] pointer-events-auto"
            >
                <!-- Icon -->
                <div 
                    class="h-8 w-8 rounded-full flex items-center justify-center shrink-0"
                    :class="{
                        'bg-emerald-500/10 text-emerald-500': notification.type === 'success',
                        'bg-red-500/10 text-red-500': notification.type === 'error',
                        'bg-blue-500/10 text-blue-500': notification.type === 'info'
                    }"
                >
                    <span class="material-symbols-outlined text-xl" x-text="notification.type === 'success' ? 'check_circle' : (notification.type === 'error' ? 'error' : 'info')"></span>
                </div>

                <!-- Message -->
                <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]" x-text="notification.message"></span>

                <!-- Close Button -->
                <button @click="remove(notification.id)" class="ml-auto text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-colors shrink-0">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
        </template>
    </div>

    @if(session('success'))
    <script>
        document.addEventListener('livewire:navigated', () => {
            window.dispatchEvent(new CustomEvent('notify', { detail: [{ type: 'success', message: "{{ session('success') }}" }] }));
        }, { once: true });
        // Also trigger on initial load if not using Livewire nav
        document.addEventListener('DOMContentLoaded', () => {
            window.dispatchEvent(new CustomEvent('notify', { detail: [{ type: 'success', message: "{{ session('success') }}" }] }));
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('livewire:navigated', () => {
            window.dispatchEvent(new CustomEvent('notify', { detail: [{ type: 'error', message: "{{ session('error') }}" }] }));
        }, { once: true });
        document.addEventListener('DOMContentLoaded', () => {
            window.dispatchEvent(new CustomEvent('notify', { detail: [{ type: 'error', message: "{{ session('error') }}" }] }));
        });
    </script>
    @endif

    @livewireScripts
    <script>
        function sidebarController() {
            return {
                sidebarCollapsed: localStorage.getItem('sidebar-collapsed') === 'true',
                openMenus: {},
                
                toggleSidebar() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('sidebar-collapsed', this.sidebarCollapsed);
                    if (this.sidebarCollapsed) {
                        this.openMenus = {};
                    }
                },

                toggleMenu(menuId) {
                    if (this.sidebarCollapsed) return;
                    this.openMenus[menuId] = !this.openMenus[menuId];
                },

                isMenuOpen(menuId) {
                    return this.openMenus[menuId] || false;
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
