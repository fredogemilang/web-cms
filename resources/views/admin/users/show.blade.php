@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
    <div class="px-6 pb-6 md:px-10 md:pb-10">
        <!-- Back Button -->
        <a href="{{ route('admin.users.index') }}" wire:navigate class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition mb-6">
            <span class="material-symbols-outlined mr-2 text-xl">arrow_back</span>
            Back to Users
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-8 space-y-8">
                <!-- User Information -->
                <section class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
                    <div class="mb-10 flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">User Information</h2>
                            <p class="text-sm text-[#6F767E] mt-1">Basic account details and identity.</p>
                        </div>
                        @can('users.edit')
                        <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-primary/10 text-primary hover:bg-primary hover:text-white font-bold rounded-xl transition-colors text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">edit</span>
                            Edit User
                        </a>
                        @endcan
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="space-y-1">
                            <label class="form-label">Full Name</label>
                            <div class="form-input-field bg-gray-50 dark:bg-[#0B0B0B] border-none text-gray-500 dark:text-gray-400 cursor-default">
                                {{ $user->name }}
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="form-label">Username</label>
                            <div class="form-input-field bg-gray-50 dark:bg-[#0B0B0B] border-none text-gray-500 dark:text-gray-400 cursor-default">
                                {{ $user->username ?? '-' }}
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-1">
                            <label class="form-label">Email Address</label>
                            <div class="form-input-field bg-gray-50 dark:bg-[#0B0B0B] border-none text-gray-500 dark:text-gray-400 cursor-default">
                                {{ $user->email }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 space-y-1">
                        <label class="form-label">Bio</label>
                        <div class="form-input-field bg-gray-50 dark:bg-[#0B0B0B] border-none text-gray-500 dark:text-gray-400 cursor-default min-h-[100px]">
                            {{ $user->bio ?? 'No bio provided.' }}
                        </div>
                    </div>
                </section>

                <!-- Roles -->
                <section class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
                    <div class="mb-10">
                        <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Assigned Roles</h2>
                        <p class="text-sm text-[#6F767E] mt-1">Roles define the user's access levels.</p>
                    </div>
                    <div class="space-y-4">
                        @forelse($user->roles as $role)
                        <div class="flex items-center p-4 rounded-2xl border border-gray-100 dark:border-[#272B30] bg-gray-50 dark:bg-[#272B30]/30">
                            <div class="w-5 h-5 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-xs">check</span>
                            </div>
                            <div class="ml-4">
                                <span class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $role->name }}</span>
                                @if($role->is_super_admin)
                                    <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold bg-red-100 text-red-700 rounded-full uppercase tracking-wider">Super Admin</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="p-6 bg-gray-50 dark:bg-[#0B0B0B] rounded-xl text-center border border-gray-100 dark:border-[#272B30]">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">person_off</span>
                            <p class="text-sm text-gray-500 font-medium">No roles assigned</p>
                        </div>
                        @endforelse
                    </div>
                </section>

                <!-- Security -->
                <section class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
                    <div class="mb-10">
                        <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Security</h2>
                        <p class="text-sm text-[#6F767E] mt-1">Account safety information.</p>
                    </div>
                    <div class="flex items-center justify-between p-6 rounded-2xl bg-gray-50 dark:bg-[#0B0B0B] border border-gray-100 dark:border-[#272B30]">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10 text-primary">
                                <span class="material-symbols-outlined">lock</span>
                            </div>
                            <div>
                                <p class="text-[15px] font-bold text-[#111827] dark:text-[#FCFCFC]">Password</p>
                                <p class="text-xs text-[#6F767E]">
                                    Last changed {{ $user->password_changed_at ? $user->password_changed_at->diffForHumans() : 'Never' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="lg:col-span-4 space-y-8">
                <!-- Profile Picture -->
                <section class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30] text-center">
                    <h2 class="form-label mb-8">Profile Picture</h2>
                    <div class="relative group inline-block">
                        <div class="h-40 w-40 rounded-3xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center border-2 border-dashed border-gray-200 dark:border-[#272B30] overflow-hidden">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Profile" class="h-full w-full object-cover">
                            @else
                                <span class="material-symbols-outlined text-5xl text-[#6F767E]">person</span>
                            @endif
                        </div>
                    </div>
                </section>

                <!-- Metadata -->
                <section class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
                    <h2 class="form-label mb-8">Account Metadata</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-[#272B30]">
                            <span class="text-sm font-medium text-[#6F767E]">Joined Date</span>
                            <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-[#6F767E]">Last Login</span>
                            <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-[#272B30]">
                            <span class="text-sm font-medium text-[#6F767E]">Status</span>
                            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Delete Action -->
                @can('users.delete')
                @if($user->id !== auth()->id())
                <section class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
                    <h2 class="form-label mb-4 text-red-500">Danger Zone</h2>
                    <p class="text-sm text-[#6F767E] mb-6">Once you delete a user, there is no going back. Please be certain.</p>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-3 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 font-bold rounded-xl transition-colors border border-red-100">
                            Delete User
                        </button>
                    </form>
                </section>
                @endif
                @endcan
            </div>
        </div>
    </div>
@endsection
