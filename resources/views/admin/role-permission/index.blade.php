@extends('layouts.admin')

@section('title', 'Role & Permission')
@section('page-title', 'Roles & Permissions')

@push('styles')
<style>
    /* Role Permission Specific Styles */
    .role-item.active {
        background-color: rgba(37, 99, 235, 0.1);
        border-color: #2563EB;
    }
    .dark .role-item.active {
        background-color: rgba(39, 43, 48, 0.5);
    }
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        transition: opacity 0.3s ease-in-out;
        opacity: 0;
    }
    .modal-overlay.show {
        opacity: 1;
    }
    .modal-content {
        transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
        transform: scale(0.95);
        opacity: 0;
    }
    .modal-overlay.show .modal-content {
        transform: scale(1);
        opacity: 1;
    }
    #toast-notification {
        transition: transform 0.3s ease, opacity 0.3s ease;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .spinner {
        animation: spin 0.8s linear infinite;
    }
    .loading-container {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
    }
    .loading-state .matrix-checkbox {
        opacity: 0;
        pointer-events: none;
    }
    .loading-indicator {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    .loading-state .loading-indicator {
        display: flex;
    }
    .loading-overlay {
        position: absolute;
        inset: 0;
        background: transparent;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<div class="flex flex-1 overflow-hidden -mx-6 md:-mx-10 border-t border-gray-200 dark:border-[#272B30]" style="height: calc(100vh - 140px);">
    <!-- Left Panel: Role List -->
    <div class="w-1/4 border-r border-gray-200 dark:border-[#272B30] flex flex-col bg-white/50 dark:bg-transparent">
        <div class="p-6 space-y-4">
            <!-- Search Roles -->
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#6F767E] text-xl">search</span>
                <input
                    id="role-search"
                    class="w-full bg-white dark:bg-[#1A1A1A] border-gray-200 dark:border-[#272B30] rounded-xl pl-10 pr-4 py-2.5 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-primary focus:border-primary"
                    placeholder="Search roles..." type="text" />
            </div>
            
            <!-- Add New Role Button -->
            @can('roles.create')
            <button
                id="btn-add-role"
                class="w-full flex items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-[#272B30] px-5 py-2.5 text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] hover:bg-gray-50 dark:hover:bg-[#1A1A1A] transition-all">
                <span class="material-symbols-outlined text-lg">add</span>
                Add New Role
            </button>
            @endcan
        </div>
        
        <!-- Role List -->
        <div class="flex-1 overflow-y-auto no-scrollbar px-4 pb-4 space-y-2" id="role-list">
            @foreach($roles as $role)
            <div
                class="role-item w-full group p-4 rounded-2xl border transition-all relative cursor-pointer {{ $selectedRole && $selectedRole->id === $role->id ? 'active bg-blue-100 dark:bg-blue-900/30 border-primary dark:border-primary' : 'bg-white dark:bg-transparent border-gray-200 dark:border-[#272B30] hover:bg-gray-50 dark:hover:bg-[#1A1A1A] ' }}"
                data-role-id="{{ $role->id }}"
                data-role-name="{{ $role->name }}"
                data-role-description="{{ $role->description }}">
                <div class="flex justify-between items-start">
                    <div class="flex flex-col mb-3">
                        <div class="flex items-center gap-2">
                        <span class="font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $role->name }}</span>
                        @if($selectedRole && $selectedRole->id === $role->id)
                        <span class="material-symbols-outlined text-primary text-lg">check_circle</span>
                        @endif
                    </div>
                    @if($role->is_super_admin)
                        <span class="inline-flex w-fit text-[10px] bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-2 py-0.5 rounded uppercase font-bold tracking-wider">Super Admin</span>
                    @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            @can('roles.create')
                            <button class="p-1 h-9 w-9 hover:bg-gray-200 dark:hover:bg-[#272B30] rounded-lg transition-colors btn-clone-role" title="Clone Role" data-role-id="{{ $role->id }}">
                                <span class="material-symbols-outlined text-sm text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]">content_copy</span>
                            </button>
                            @endcan
                            @can('roles.edit')
                            <button class="p-1 h-9 w-9 hover:bg-gray-200 dark:hover:bg-[#272B30] rounded-lg transition-colors btn-edit-role" title="Edit Role" data-role-id="{{ $role->id }}">
                                <span class="material-symbols-outlined text-sm text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]">edit</span>
                            </button>
                            @endcan
                            @can('roles.delete')
                            @if($role->users_count === 0 && !$role->is_super_admin)
                            <button class="p-1 h-9 w-9 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors btn-delete-role" title="Delete Role" data-role-id="{{ $role->id }}">
                                <span class="material-symbols-outlined text-sm text-red-500">delete</span>
                            </button>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>
                <p class="text-xs text-[#6F767E] line-clamp-2">{{ $role->description ?: 'No description' }}</p>
                <div class="flex items-center gap-3 mt-2 text-xs text-[#6F767E]">
                    <span>{{ $role->users_count }} users</span>
                    <span>•</span>
                    <span>{{ $role->permissions_count }} permissions</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Right Panel: Permission Matrix -->
    <div class="w-3/4 flex flex-col bg-white dark:bg-[#0B0B0B] overflow-hidden">
        @if($selectedRole)
        <div class="px-8 py-6 border-b border-gray-200 dark:border-[#272B30] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-xl">edit_note</span>
                </div>
                <h2 class="font-bold text-lg text-[#111827] dark:text-[#FCFCFC]">
                    Editing Permissions: <span class="text-primary" id="selected-role-name">{{ $selectedRole->name }}</span>
                </h2>
            </div>
            @if($selectedRole->is_super_admin)
            <span class="text-xs text-red-600 bg-red-100 dark:bg-red-900/30 px-3 py-1 rounded-full font-semibold">
                Super Admin - Full Access
            </span>
            @endif
        </div>
        
        <div class="flex-1 overflow-y-auto p-8 no-scrollbar">
            @if($selectedRole->is_super_admin)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 mb-6">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-600 text-2xl">shield</span>
                    <div>
                        <p class="font-bold text-red-900 dark:text-red-300">Super Admin Role</p>
                        <p class="text-sm text-red-700 dark:text-red-400">This role automatically has access to all permissions. Permission assignment is not required.</p>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-200 dark:border-[#272B30] overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-[#272B30]">
                            <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">
                                Capability Module
                            </th>
                            <th class="px-4 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-center">
                                View
                            </th>
                            <th class="px-4 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-center">
                                Create
                            </th>
                            <th class="px-4 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-center">
                                Edit
                            </th>
                            <th class="px-4 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-center">
                                Delete
                            </th>
                        </tr>
                    </thead>
                    <tbody id="permission-matrix">
                        <!-- CORE MODULES Section -->
                        <tr class="bg-emerald-50 dark:bg-emerald-900/10">
                            <td class="px-6 py-3" colspan="5">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-emerald-600 text-lg">verified</span>
                                    <span class="text-[11px] font-black text-emerald-700 dark:text-emerald-400 tracking-widest uppercase">Core Modules</span>
                                </div>
                            </td>
                        </tr>
                        
                        @foreach($modules['core'] ?? [] as $module)
                        <!-- Module Row -->
                        <tr class="border-b border-gray-200 dark:border-[#272B30]/50 hover:bg-gray-50 dark:hover:bg-[#272B30]/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @php
                                        $modulePermission = ($permissions[$module] ?? collect())->first();
                                        $icon = $modulePermission->icon ?? null;
                                    @endphp
                                    <span class="material-symbols-outlined text-[#6F767E] text-lg">
                                        @if($icon)
                                            {{ $icon }}
                                        @else
                                            @switch($module)
                                                @case('dashboard') layers @break
                                                @case('users') group @break
                                                @case('roles') shield @break
                                                @case('permissions') lock @break
                                                @case('menus') menu @break
                                                @case('pages') article @break
                                                @case('posts') rss_feed @break
                                                @default layers
                                            @endswitch
                                        @endif
                                    </span>
                                    <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC] capitalize">{{ str_replace('.', ' › ', $module) }}</span>
                                </div>
                            </td>
                            @php
                                $actions = ['view', 'create', 'edit', 'delete'];
                                $modulePermissions = $permissions[$module] ?? collect();
                            @endphp
                            @foreach($actions as $action)
                                @php
                                    $permission = $modulePermissions->firstWhere('action', $action);
                                    $hasPermission = $permission && $selectedRole->permissions->contains($permission->id);
                                @endphp
                                <td class="px-4 py-4 text-center">
                                    @if($permission)
                                    <div class="loading-container">
                                        <input
                                            type="checkbox"
                                            class="h-5 w-5 rounded bg-blue-50 dark:bg-[#0B0B0B] border-blue-300 dark:border-[#272B30] text-primary checked:bg-primary checked:border-primary dark:checked:bg-primary dark:checked:border-primary focus:ring-primary matrix-checkbox"
                                            data-role-id="{{ $selectedRole->id }}"
                                            data-permission-id="{{ $permission->id }}"
                                            {{ $hasPermission ? 'checked' : '' }}
                                            {{ $selectedRole->is_super_admin ? 'disabled' : '' }}
                                            @if(!$selectedRole->is_super_admin)
                                            onchange="togglePermission(this)"
                                            @endif
                                        />
                                        <div class="loading-indicator">
                                            <svg class="spinner w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-[#6F767E]">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                        
                        @if(!empty($modules['plugins']))
                        <!-- PLUGINS Section -->
                        <tr class="bg-violet-50 dark:bg-violet-900/10">
                            <td class="px-6 py-3" colspan="5">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-violet-600 text-lg">extension</span>
                                    <span class="text-[11px] font-black text-violet-700 dark:text-violet-400 tracking-widest uppercase">Plugin Modules</span>
                                </div>
                            </td>
                        </tr>
                        
                        @foreach($modules['plugins'] as $pluginSlug => $pluginModules)
                        <!-- Plugin Group Header -->
                        <tr class="bg-violet-50/50 dark:bg-violet-900/5">
                            <td class="px-6 py-2" colspan="5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs px-2 py-0.5 bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 rounded font-semibold uppercase">{{ $pluginSlug }}</span>
                                </div>
                            </td>
                        </tr>
                        
                        @foreach($pluginModules as $module)
                        <tr class="border-b border-gray-200 dark:border-[#272B30]/50 hover:bg-gray-50 dark:hover:bg-[#272B30]/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @php
                                        $modulePermission = ($permissions[$module] ?? collect())->first();
                                        $icon = $modulePermission->icon ?? 'extension';
                                    @endphp
                                    <span class="material-symbols-outlined text-violet-500 text-lg">{{ $icon }}</span>
                                    <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC] capitalize">{{ str_replace('.', ' › ', $module) }}</span>
                                </div>
                            </td>
                            @php
                                $actions = ['view', 'create', 'edit', 'delete'];
                                $modulePermissions = $permissions[$module] ?? collect();
                            @endphp
                            @foreach($actions as $action)
                                @php
                                    $permission = $modulePermissions->firstWhere('action', $action);
                                    $hasPermission = $permission && $selectedRole->permissions->contains($permission->id);
                                @endphp
                                <td class="px-4 py-4 text-center">
                                    @if($permission)
                                    <div class="loading-container">
                                        <input
                                            type="checkbox"
                                            class="h-5 w-5 rounded bg-violet-50 dark:bg-[#0B0B0B] border-violet-300 dark:border-[#272B30] text-violet-600 checked:bg-violet-600 checked:border-violet-600 dark:checked:bg-violet-600 dark:checked:border-violet-600 focus:ring-violet-500 matrix-checkbox"
                                            data-role-id="{{ $selectedRole->id }}"
                                            data-permission-id="{{ $permission->id }}"
                                            {{ $hasPermission ? 'checked' : '' }}
                                            {{ $selectedRole->is_super_admin ? 'disabled' : '' }}
                                            @if(!$selectedRole->is_super_admin)
                                            onchange="togglePermission(this)"
                                            @endif
                                        />
                                        <div class="loading-indicator">
                                            <svg class="spinner w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-[#6F767E]">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <span class="material-symbols-outlined text-6xl text-[#6F767E] mb-4">shield</span>
                <p class="text-[#6F767E] font-medium">Select a role to manage permissions</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Toast Notification -->
<div class="fixed bottom-8 right-8 z-[60] flex items-center gap-3 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-xl px-4 py-3 shadow-2xl translate-y-20 opacity-0 pointer-events-none transition-all duration-300"
    id="toast-notification">
    <div class="h-8 w-8 rounded-full bg-emerald-500/10 flex items-center justify-center" id="toast-icon">
        <span class="material-symbols-outlined text-emerald-500 text-xl">check_circle</span>
    </div>
    <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]" id="toast-message">Permission updated successfully</span>
    <button class="ml-4 text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]" onclick="hideToast()">
        <span class="material-symbols-outlined text-lg">close</span>
    </button>
</div>

<!-- Add Role Modal -->
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay hidden" id="add-role-modal">
    <div class="bg-white dark:bg-[#1A1A1A] w-full max-w-md rounded-2xl border border-gray-200 dark:border-[#272B30] shadow-2xl overflow-hidden modal-content">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-[#272B30] flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]" id="modal-title">Add New Role</h3>
            <button class="text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-colors" onclick="closeRoleModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="role-form">
            @csrf
            <input type="hidden" id="role-id" name="role_id" value="">
            <div class="p-6 space-y-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#6F767E] uppercase tracking-widest">Role Name</label>
                    <input
                        id="role-name"
                        name="name"
                        class="w-full bg-gray-50 dark:bg-[#0B0B0B] border-gray-200 dark:border-[#272B30] rounded-xl px-4 py-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-primary focus:border-primary placeholder-[#6F767E]"
                        placeholder="e.g. Content Curator" type="text" required />
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#6F767E] uppercase tracking-widest">Role Description</label>
                    <textarea
                        id="role-description"
                        name="description"
                        class="w-full bg-gray-50 dark:bg-[#0B0B0B] border-gray-200 dark:border-[#272B30] rounded-xl px-4 py-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-primary focus:border-primary placeholder-[#6F767E] resize-none h-24"
                        placeholder="Briefly describe this role's responsibilities..."></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-[#272B30]/20 flex items-center justify-end gap-3">
                <button type="button"
                    class="px-5 py-2 text-sm font-semibold text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-colors"
                    onclick="closeRoleModal()">Cancel</button>
                <button type="submit"
                    class="px-6 py-2 bg-primary rounded-xl text-sm font-bold text-white hover:bg-blue-600 transition-all shadow-lg shadow-primary/20"
                    id="modal-submit-btn">Create Role</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay hidden" id="delete-role-modal">
    <div class="bg-white dark:bg-[#1A1A1A] w-full max-w-md rounded-2xl border border-gray-200 dark:border-[#272B30] shadow-2xl overflow-hidden modal-content">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-[#272B30] flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Delete Role</h3>
            <button class="text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-colors" onclick="closeDeleteModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-6">
            <p class="text-[#111827] dark:text-[#FCFCFC]">Are you sure you want to delete this role? This action cannot be undone.</p>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-[#272B30]/20 flex items-center justify-end gap-3">
            <button type="button"
                class="px-5 py-2 text-sm font-semibold text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-colors"
                onclick="closeDeleteModal()">Cancel</button>
            <button type="button"
                class="px-6 py-2 bg-red-600 rounded-xl text-sm font-bold text-white hover:bg-red-700 transition-all shadow-lg shadow-red-600/20"
                id="confirm-delete-btn">Delete Role</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    // Base URLs with placeholder role id (will be replaced in JS)
    const baseUrls = {
        clone: '{{ route('admin.role-permission.clone-role', ['role' => '__ROLE_ID__']) }}',
        delete: '{{ route('admin.role-permission.delete-role', ['role' => '__ROLE_ID__']) }}',
        update: '{{ route('admin.role-permission.update-role', ['role' => '__ROLE_ID__']) }}',
        toggle: '{{ route('admin.role-permission.toggle-permission', ['role' => '__ROLE_ID__']) }}',
        store: '{{ route('admin.role-permission.store-role') }}',
        index: '{{ route('admin.role-permission.index') }}'
    };
    // Debug: Log URLs to console
    console.log('Base URLs:', baseUrls);
    
    let toastTimeout;
    let currentDeleteRoleId = null;

    // Modal Animation Helper
    function toggleModal(modalId, show) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        if (show) {
            modal.classList.remove('hidden');
            // Force reflow
            void modal.offsetWidth;
            modal.classList.add('show');
        } else {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); // Match transition duration
        }
    }

    // Role Selection
    document.querySelectorAll('.role-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.closest('.btn-clone-role') || e.target.closest('.btn-edit-role') || e.target.closest('.btn-delete-role')) {
                return;
            }
            const roleId = this.dataset.roleId;
            window.location.href = `${baseUrls.index}?role=${roleId}`;
        });
    });

    // Role Search
    document.getElementById('role-search').addEventListener('input', function(e) {
        const search = e.target.value.toLowerCase();
        document.querySelectorAll('.role-item').forEach(item => {
            const name = item.dataset.roleName.toLowerCase();
            const desc = item.dataset.roleDescription.toLowerCase();
            if (name.includes(search) || desc.includes(search)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Add Role Button
    const btnAddRole = document.getElementById('btn-add-role');
    if (btnAddRole) {
        btnAddRole.addEventListener('click', function() {
            document.getElementById('modal-title').textContent = 'Add New Role';
            document.getElementById('modal-submit-btn').textContent = 'Create Role';
            document.getElementById('role-id').value = '';
            document.getElementById('role-name').value = '';
            document.getElementById('role-description').value = '';
            toggleModal('add-role-modal', true);
        });
    }

    // Edit Role Buttons
    document.querySelectorAll('.btn-edit-role').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const roleItem = this.closest('.role-item');
            document.getElementById('modal-title').textContent = 'Edit Role';
            document.getElementById('modal-submit-btn').textContent = 'Update Role';
            document.getElementById('role-id').value = roleItem.dataset.roleId;
            document.getElementById('role-name').value = roleItem.dataset.roleName;
            document.getElementById('role-description').value = roleItem.dataset.roleDescription;
            toggleModal('add-role-modal', true);
        });
    });

    // Clone Role Buttons
    document.querySelectorAll('.btn-clone-role').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const roleId = this.dataset.roleId;
            
            try {
                const response = await fetch(baseUrls.clone.replace('__ROLE_ID__', roleId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    showToast(errorData.message || 'Failed to clone role', 'error');
                    return;
                }
                
                const data = await response.json();
                
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = `${baseUrls.index}?role=${data.role.id}`;
                    }, 500);
                } else {
                    showToast(data.message || 'Failed to clone role', 'error');
                }
            } catch (error) {
                console.error('Clone role error:', error);
                showToast('An error occurred while cloning role', 'error');
            }
        });
    });

    // Delete Role Buttons
    document.querySelectorAll('.btn-delete-role').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentDeleteRoleId = this.dataset.roleId;
            toggleModal('delete-role-modal', true);
        });
    });

    // Confirm Delete
    document.getElementById('confirm-delete-btn').addEventListener('click', async function() {
        if (!currentDeleteRoleId) return;
        
        try {
            const response = await fetch(baseUrls.delete.replace('__ROLE_ID__', currentDeleteRoleId), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                showToast(errorData.message || 'Failed to delete role', 'error');
                return;
            }
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                closeDeleteModal();
                setTimeout(() => {
                    window.location.href = baseUrls.index;
                }, 500);
            } else {
                showToast(data.message || 'Failed to delete role', 'error');
            }
        } catch (error) {
            console.error('Delete role error:', error);
            showToast('An error occurred while deleting role', 'error');
        }
    });

    function closeDeleteModal() {
        toggleModal('delete-role-modal', false);
        currentDeleteRoleId = null;
    }

    // Role Form Submit
    document.getElementById('role-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const roleId = document.getElementById('role-id').value;
        const name = document.getElementById('role-name').value;
        const description = document.getElementById('role-description').value;
        
        const url = roleId 
            ? baseUrls.update.replace('__ROLE_ID__', roleId)
            : baseUrls.store;
        
        const method = roleId ? 'PUT' : 'POST';
        
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name, description })
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                // Handle validation errors
                if (errorData.errors) {
                    const firstError = Object.values(errorData.errors)[0];
                    showToast(Array.isArray(firstError) ? firstError[0] : firstError, 'error');
                } else {
                    showToast(errorData.message || 'Failed to save role', 'error');
                }
                return;
            }
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                closeRoleModal();
                setTimeout(() => {
                    window.location.href = `${baseUrls.index}?role=${data.role.id}`;
                }, 500);
            } else {
                showToast(data.message || 'Failed to save role', 'error');
            }
        } catch (error) {
            console.error('Save role error:', error);
            showToast('An error occurred while saving role', 'error');
        }
    });

    function closeRoleModal() {
        toggleModal('add-role-modal', false);
    }

    // Permission Toggle
    async function togglePermission(checkbox) {
        const container = checkbox.closest('.loading-container');
        const roleId = checkbox.dataset.roleId;
        const permissionId = checkbox.dataset.permissionId;
        
        container.classList.add('loading-state');
        
        try {
            const response = await fetch(baseUrls.toggle.replace('__ROLE_ID__', roleId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ permission_id: permissionId })
            });
            
            const data = await response.json();
            
            container.classList.remove('loading-state');
            
            if (data.success) {
                checkbox.checked = data.attached;
                showToast(data.message, 'success');
            } else {
                checkbox.checked = !checkbox.checked; // Revert
                showToast(data.message || 'An error occurred', 'error');
            }
        } catch (error) {
            container.classList.remove('loading-state');
            checkbox.checked = !checkbox.checked; // Revert
            showToast('An error occurred', 'error');
        }
    }

    // Toast Functions
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast-notification');
        const icon = document.getElementById('toast-icon');
        const messageEl = document.getElementById('toast-message');
        
        messageEl.textContent = message;
        
        if (type === 'success') {
            icon.innerHTML = '<span class="material-symbols-outlined text-emerald-500 text-xl">check_circle</span>';
            icon.className = 'h-8 w-8 rounded-full bg-emerald-500/10 flex items-center justify-center';
        } else {
            icon.innerHTML = '<span class="material-symbols-outlined text-red-500 text-xl">error</span>';
            icon.className = 'h-8 w-8 rounded-full bg-red-500/10 flex items-center justify-center';
        }
        
        toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            hideToast();
        }, 3000);
    }

    function hideToast() {
        const toast = document.getElementById('toast-notification');
        toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
    }
</script>
@endpush
