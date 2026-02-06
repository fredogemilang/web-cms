@php
    $menuBuilder = app(\App\Services\AdminMenuBuilder::class);
    $menus = collect($menuBuilder->build());
    $currentRoute = request()->route()->getName();
@endphp

@foreach($menus as $menu)
    @if(empty($menu['children']))
        {{-- Single Menu Item --}}
        <a 
            href="{{ isset($menu['route']) && $menu['route'] ? route($menu['route']) : ($menu['url'] ?? '#') }}" 
            class="flex items-center px-6 py-3 mb-1 rounded-full transition-all duration-200 group {{ str_starts_with($currentRoute, str_replace('.index', '', $menu['route'] ?? '')) ? 'bg-white text-gray-900 shadow-lg transform scale-105' : 'text-gray-400 hover:bg-white/10 hover:text-white hover:shadow-md' }}"
        >
            @if(isset($menu['icon']) && $menu['icon'])
                @switch($menu['icon'])
                    @case('home')
                        <svg class="w-5 h-5 mr-3 {{ str_starts_with($currentRoute, str_replace('.index', '', $menu['route'] ?? '')) ? 'text-gray-900' : 'text-gray-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        @break
                    @case('users')
                        <svg class="w-5 h-5 mr-3 {{ str_starts_with($currentRoute, str_replace('.index', '', $menu['route'] ?? '')) ? 'text-gray-900' : 'text-gray-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        @break
                    @case('user')
                        <svg class="w-5 h-5 mr-3 {{ str_starts_with($currentRoute, str_replace('.index', '', $menu['route'] ?? '')) ? 'text-gray-900' : 'text-gray-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        @break
                    @case('shield')
                        <svg class="w-5 h-5 mr-3 {{ str_starts_with($currentRoute, str_replace('.index', '', $menu['route'] ?? '')) ? 'text-gray-900' : 'text-gray-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        @break
                    @case('lock')
                        <svg class="w-5 h-5 mr-3 {{ str_starts_with($currentRoute, str_replace('.index', '', $menu['route'] ?? '')) ? 'text-gray-900' : 'text-gray-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        @break
                    @case('menu')
                        <svg class="w-5 h-5 mr-3 {{ str_starts_with($currentRoute, str_replace('.index', '', $menu['route'] ?? '')) ? 'text-gray-900' : 'text-gray-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        @break
                    @default
                        <span class="material-symbols-outlined w-5 h-5 mr-3 text-[20px] {{ str_starts_with($currentRoute, str_replace('.index', '', $menu['route'] ?? '')) ? 'text-gray-900' : 'text-gray-500 group-hover:text-white' }}">
                            {{ $menu['icon'] }}
                        </span>
                @endswitch
            @endif
            <span class="font-medium">{{ $menu['title'] }}</span>
        </a>
    @else
        {{-- Menu with Submenu --}}
        <div x-data="{ open: {{ str_contains($currentRoute, 'users') || str_contains($currentRoute, 'roles') || str_contains($currentRoute, 'permissions') ? 'true' : 'false' }} }">
            <button 
                @click="open = !open"
                class="w-full flex items-center justify-between px-6 py-3 mb-1 rounded-full transition-all duration-200 group text-gray-400 hover:bg-white/10 hover:shadow-md hover:text-white"
            >
                <div class="flex items-center">
                    @if(isset($menu['icon']) && $menu['icon'])
                        @switch($menu['icon'])
                            @case('users')
                                <svg class="w-5 h-5 mr-3 text-gray-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                @break
                            @default
                                <span class="material-symbols-outlined w-5 h-5 mr-3 text-[20px] text-gray-500 group-hover:text-white">
                                    {{ $menu['icon'] }}
                                </span>
                        @endswitch
                    @endif
                    <span class="font-medium">{{ $menu['title'] }}</span>
                </div>
                <svg 
                    class="w-4 h-4 transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            {{-- Submenu Items --}}
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="ml-4 pl-4 border-l-2 border-white/10 space-y-1 my-2"
            >
                @foreach($menu['children'] as $child)
                    <a 
                        href="{{ isset($child['route']) && $child['route'] ? route($child['route']) : ($child['url'] ?? '#') }}" 
                        class="flex items-center px-4 py-2 rounded-full transition-all duration-200 text-sm {{ str_starts_with($currentRoute, str_replace('.index', '', $child['route'] ?? '')) ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-400 hover:text-white hover:bg-white/10' }}"
                    >
                        @if(isset($child['icon']) && $child['icon'])
                            @switch($child['icon'])
                                @case('user')
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    @break
                                @case('shield')
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    @break
                                @case('lock')
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    @break
                                @default
                                    <span class="material-symbols-outlined w-4 h-4 mr-3 text-[16px]">{{ $child['icon'] }}</span>
                            @endswitch
                        @endif
                        <span>{{ $child['title'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endforeach
