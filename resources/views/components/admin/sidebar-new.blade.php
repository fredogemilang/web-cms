<ul class="space-y-1">
    <!-- Main Section -->
    <li class="px-4 pt-4 pb-2">
        <span class="text-[10px] font-bold text-[#6F767E] uppercase tracking-widest sidebar-text">Main</span>
    </li>
    <li>
        <a wire:navigate class="flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 nav-item overflow-hidden {{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC]' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}"
            href="{{ route('admin.dashboard') }}">
            <span class="material-symbols-outlined shrink-0">dashboard</span>
            <span class="font-semibold text-[15px] sidebar-text">Dashboard</span>
        </a>
    </li>

    <!-- Content Section -->
    <li class="px-4 pt-4 pb-2">
        <span class="text-[10px] font-bold text-[#6F767E] uppercase tracking-widest sidebar-text">Content</span>
    </li>
    <li x-data="{ open: {{ request()->routeIs('admin.pages.*') ? 'true' : 'false' }} }">
        <button
            @click="open = !open; $dispatch('submenu-toggle')"
            class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined shrink-0">article</span>
                <span class="font-semibold text-[15px] sidebar-text">Pages</span>
            </div>
            <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
        </button>
        <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 200px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <ul class="submenu-list mt-1 space-y-1">
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 hover:bg-white hover:shadow-sm dark:hover:bg-[#272B30] dark:hover:shadow-none {{ request()->routeIs('admin.pages.index') ? 'text-[#2563EB] font-semibold dark:text-[#FCFCFC] dark:bg-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}" href="{{ route('admin.pages.index') }}">
                        <span class="text-[14px] font-medium">All Pages</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 hover:bg-white hover:shadow-sm dark:hover:bg-[#272B30] dark:hover:shadow-none {{ request()->routeIs('admin.pages.create') ? 'text-[#2563EB] font-semibold dark:text-[#FCFCFC] dark:bg-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}" href="{{ route('admin.pages.create') }}">
                        <span class="text-[14px] font-medium">Add Page</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>

    {{-- Dynamic CPT Menus --}}
    @php
        $cpts = \App\Models\CustomPostType::active()->inMenu()->get();
    @endphp

    @foreach($cpts as $cpt)
    @php
        $cptTaxonomies = $cpt->taxonomies();
        $isCptActive = (request()->routeIs('admin.cpt.entries.*') && request()->route('postTypeSlug') === $cpt->slug);
        $isTaxonomyActive = (request()->routeIs('admin.taxonomies.terms.*') && $cptTaxonomies->where('id', request()->route('taxonomy'))->isNotEmpty());
    @endphp
    <li x-data="{ open: {{ $isCptActive || $isTaxonomyActive ? 'true' : 'false' }} }">
        <button
            @click="open = !open; $dispatch('submenu-toggle')"
            class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined shrink-0">{{ $cpt->icon ?? 'article' }}</span>
                <span class="font-semibold text-[15px] sidebar-text">{{ $cpt->plural_label }}</span>
            </div>
            <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
        </button>
        <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 500px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <ul class="submenu-list mt-1 space-y-1">
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 hover:bg-white hover:shadow-sm dark:hover:bg-[#272B30] dark:hover:shadow-none {{ (request()->routeIs('admin.cpt.entries.index') && request()->route('postTypeSlug') === $cpt->slug) ? 'text-[#2563EB] font-semibold dark:text-[#FCFCFC] dark:bg-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}" 
                       href="{{ route('admin.cpt.entries.index', $cpt->slug) }}">
                        <span class="text-[14px] font-medium">All {{ $cpt->plural_label }}</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 hover:bg-white hover:shadow-sm dark:hover:bg-[#272B30] dark:hover:shadow-none {{ (request()->routeIs('admin.cpt.entries.create') && request()->route('postTypeSlug') === $cpt->slug) ? 'text-[#2563EB] font-semibold dark:text-[#FCFCFC] dark:bg-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}" 
                       href="{{ route('admin.cpt.entries.create', $cpt->slug) }}">
                        <span class="text-[14px] font-medium">Add {{ $cpt->singular_label }}</span>
                    </a>
                </li>
                
                {{-- Taxonomies --}}
                @foreach($cptTaxonomies as $taxonomy)
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 hover:bg-white hover:shadow-sm dark:hover:bg-[#272B30] dark:hover:shadow-none {{ (request()->routeIs('admin.taxonomies.terms.*') && request()->route('taxonomy') == $taxonomy->id) ? 'text-[#2563EB] font-semibold dark:text-[#FCFCFC] dark:bg-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}" 
                       href="{{ route('admin.taxonomies.terms.index', $taxonomy->id) }}">
                        <span class="text-[14px] font-medium">{{ $taxonomy->plural_label }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </li>
    @endforeach
    @can('media.view')
    <li x-data="{ open: {{ request()->routeIs('admin.media.*') ? 'true' : 'false' }} }">
        <button
            @click="open = !open"
            class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined shrink-0">perm_media</span>
                <span class="font-semibold text-[15px] sidebar-text">Media</span>
            </div>
            <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
        </button>
        <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 200px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <ul class="submenu-list mt-1 space-y-1">
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.media.index') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" href="{{ route('admin.media.index') }}">
                        <span class="text-[14px] font-medium">Library</span>
                    </a>
                </li>
                @can('media.upload')
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.media.create') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" href="{{ route('admin.media.create') }}">
                        <span class="text-[14px] font-medium">Add Media</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
    @endcan

    {{-- Forms Menu --}}
    @can('forms.view')
    <li x-data="{ open: {{ request()->routeIs('admin.forms.*') ? 'true' : 'false' }} }">
        <button
            @click="open = !open"
            class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined shrink-0">description</span>
                <span class="font-semibold text-[15px] sidebar-text">Forms</span>
            </div>
            <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
        </button>
        <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 200px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <ul class="submenu-list mt-1 space-y-1">
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.forms.index') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" href="{{ route('admin.forms.index') }}">
                        <span class="text-[14px] font-medium">All Forms</span>
                    </a>
                </li>
                @can('forms.create')
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.forms.create') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" href="{{ route('admin.forms.create') }}">
                        <span class="text-[14px] font-medium">Create Form</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
    @endcan

    {{-- Dynamic Plugin Menus (PRD Section 9.1) --}}
    @php
        $pluginMenuEvent = new \App\Events\RenderAdminMenu();
        event($pluginMenuEvent);
        $pluginMenus = collect($pluginMenuEvent->getMenuItems())->filter(fn($item) => str_starts_with($item['source'] ?? '', 'plugin:'));
    @endphp
    
    @if($pluginMenus->isNotEmpty())
    <li class="px-4 pt-4 pb-2">
        <span class="text-[10px] font-bold text-[#6F767E] uppercase tracking-widest sidebar-text">Plugins</span>
    </li>
    @foreach($pluginMenus as $pluginMenu)
        @can($pluginMenu['permission'] ?? '')
        <li x-data="{ open: {{ request()->routeIs($pluginMenu['route'] . '*') ? 'true' : 'false' }} }">
            @if(!empty($pluginMenu['children']))
                <button
                    @click="open = !open"
                    class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined shrink-0">{{ $pluginMenu['icon'] ?? 'extension' }}</span>
                        <span class="font-semibold text-[15px] sidebar-text">{{ $pluginMenu['title'] }}</span>
                    </div>
                    <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
                </button>
                <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 300px; opacity: 1' : 'max-height: 0; opacity: 0'">
                    <ul class="submenu-list mt-1 space-y-1">
                        @foreach($pluginMenu['children'] as $child)
                            @can($child['permission'] ?? '')
                            <li class="relative pl-6 py-1">
                                <div class="submenu-item-connector"></div>
                                <a class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs($child['route']) ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" 
                                   href="{{ $child['url'] ?? '#' }}">
                                    <span class="text-[14px] font-medium">{{ $child['title'] }}</span>
                                </a>
                            </li>
                            @endcan
                        @endforeach
                    </ul>
                </div>
            @else
                <a class="flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 nav-item overflow-hidden {{ request()->routeIs($pluginMenu['route']) ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC]' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}"
                    href="{{ $pluginMenu['url'] ?? '#' }}">
                    <span class="material-symbols-outlined shrink-0">{{ $pluginMenu['icon'] ?? 'extension' }}</span>
                    <span class="font-semibold text-[15px] sidebar-text">{{ $pluginMenu['title'] }}</span>
                </a>
            @endif
        </li>
        @endcan
    @endforeach
    @endif

    <!-- Management Section -->
    <li class="px-4 pt-4 pb-2">
        <span class="text-[10px] font-bold text-[#6F767E] uppercase tracking-widest sidebar-text">Management</span>
    </li>
    <!-- CPT Menu -->
    <li x-data="{ open: {{ request()->routeIs('admin.cpt.index') || request()->routeIs('admin.cpt.create') || request()->routeIs('admin.cpt.edit') || (request()->routeIs('admin.taxonomies.*') && !request()->routeIs('admin.taxonomies.terms.*')) ? 'true' : 'false' }} }">
        <button
            @click="open = !open"
            class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined shrink-0">layers</span>
                <span class="font-semibold text-[15px] sidebar-text">CPT</span>
            </div>
            <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
        </button>
        <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 200px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <ul class="submenu-list mt-1 space-y-1">
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.cpt.*') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" href="{{ route('admin.cpt.index') }}">
                        <span class="text-[14px] font-medium">Post Types</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ (request()->routeIs('admin.taxonomies.*') && !request()->routeIs('admin.taxonomies.terms.*')) ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" href="{{ route('admin.taxonomies.index') }}">
                        <span class="text-[14px] font-medium">Taxonomies</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>

    @canany(['users.view', 'users.create', 'menus.view'])
    <li x-data="{ open: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.profile.*') || request()->routeIs('admin.role-permission.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.menus.*') ? 'true' : 'false' }} }">
        <button
            @click="open = !open"
            class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined shrink-0">group</span>
                <span class="font-semibold text-[15px] sidebar-text">User</span>
            </div>
            <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
        </button>
        <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 350px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <ul class="submenu-list mt-1 space-y-1">
                @can('users.view')
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.users.index') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" 
                       href="{{ route('admin.users.index') }}">
                        <span class="text-[14px] font-medium">All Users</span>
                    </a>
                </li>
                @endcan
                @can('users.create')
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.users.create') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" 
                       href="{{ route('admin.users.create') }}">
                        <span class="text-[14px] font-medium">Add User</span>
                    </a>
                </li>
                @endcan
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.profile.*') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" 
                       href="{{ route('admin.profile.index') }}">
                        <span class="text-[14px] font-medium">Profile</span>
                    </a>
                </li>
                @can('roles.view')
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.role-permission.*') || request()->routeIs('admin.roles.*') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" 
                       href="{{ route('admin.role-permission.index') }}">
                        <span class="text-[14px] font-medium">Role & Permission</span>
                    </a>
                </li>
                @endcan
                @can('menus.view')
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.menus.*') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}" 
                       href="{{ route('admin.menus.index') }}">
                        <span class="text-[14px] font-medium">Menu Access</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
    @endcanany

    <!-- System Section -->
    <li class="px-4 pt-4 pb-2">
        <span class="text-[10px] font-bold text-[#6F767E] uppercase tracking-widest sidebar-text">System</span>
    </li>
    @can('plugins.view')
    <li>
        <a wire:navigate class="flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 nav-item overflow-hidden {{ request()->routeIs('admin.plugins.*') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC]' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}"
            href="{{ route('admin.plugins.index') }}">
            <span class="material-symbols-outlined shrink-0">extension</span>
            <span class="font-semibold text-[15px] sidebar-text">Plugins</span>
        </a>
    </li>
    @endcan
    @can('themes.view')
    <li x-data="{ open: {{ request()->routeIs('admin.themes.*') ? 'true' : 'false' }} }">
        <button
            @click="open = !open"
            class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined shrink-0">palette</span>
                <span class="font-semibold text-[15px] sidebar-text">Appearance</span>
            </div>
            <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
        </button>
        <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 200px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <ul class="submenu-list mt-1 space-y-1">
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a wire:navigate class="flex items-center rounded-xl px-4 py-2.5 transition-all duration-200 relative z-10 {{ request()->routeIs('admin.themes.*') ? 'bg-blue-100 text-[#2563EB] dark:bg-[#272B30] dark:text-[#FCFCFC] font-semibold' : 'text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none' }}"
                       href="{{ route('admin.themes.index') }}">
                        <span class="text-[14px] font-medium">Themes</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    @endcan
    <li x-data="{ open: false }">
        <button
            @click="open = !open"
            class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined shrink-0">settings</span>
                <span class="font-semibold text-[15px] sidebar-text">Settings</span>
            </div>
            <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
        </button>
        <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 400px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <ul class="submenu-list mt-1 space-y-1">
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a class="flex items-center rounded-xl px-4 py-2.5 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 relative z-10" href="#">
                        <span class="text-[14px] font-medium">General</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a class="flex items-center rounded-xl px-4 py-2.5 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 relative z-10" href="#">
                        <span class="text-[14px] font-medium">Brevo API</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a class="flex items-center rounded-xl px-4 py-2.5 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 relative z-10" href="#">
                        <span class="text-[14px] font-medium">Languages</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a class="flex items-center rounded-xl px-4 py-2.5 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 relative z-10" href="#">
                        <span class="text-[14px] font-medium">SEO</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a class="flex items-center rounded-xl px-4 py-2.5 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 relative z-10" href="#">
                        <span class="text-[14px] font-medium">Redirect</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    
    <!-- Litespeed Cache Menu -->
    <li x-data="{ open: false }">
        <button
            @click="open = !open"
            class="w-full group flex items-center justify-between rounded-xl px-4 py-3 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 cursor-pointer focus:outline-none nav-item overflow-hidden">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined shrink-0">bolt</span>
                <span class="font-semibold text-[15px] sidebar-text">Litespeed Cache</span>
            </div>
            <span class="material-symbols-outlined text-xl transition-transform duration-300 expand-icon" :class="{ 'rotate-180': open }">expand_more</span>
        </button>
        <div class="submenu-container overflow-hidden" :style="open ? 'max-height: 300px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <ul class="submenu-list mt-1 space-y-1">
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a class="flex items-center rounded-xl px-4 py-2.5 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 relative z-10" href="#">
                        <span class="text-[14px] font-medium">Cache</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a class="flex items-center rounded-xl px-4 py-2.5 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 relative z-10" href="#">
                        <span class="text-[14px] font-medium">CDN</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a class="flex items-center rounded-xl px-4 py-2.5 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 relative z-10" href="#">
                        <span class="text-[14px] font-medium">Image Optimization</span>
                    </a>
                </li>
                <li class="relative pl-6 py-1">
                    <div class="submenu-item-connector"></div>
                    <a class="flex items-center rounded-xl px-4 py-2.5 text-[#6F767E] hover:text-[#111827] hover:bg-white hover:shadow-sm dark:hover:text-[#FCFCFC] dark:hover:bg-[#272B30] dark:hover:shadow-none transition-all duration-200 relative z-10" href="#">
                        <span class="text-[14px] font-medium">Page Optimization</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>
