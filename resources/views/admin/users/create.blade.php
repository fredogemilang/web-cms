@extends('layouts.admin')

@section('title', 'Add New User')
@section('page-title', 'Add New User')

@section('content')
<form id="create-user-form" action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 space-y-8">
            <section
                class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Account Details</h2>
                    <p class="text-sm text-[#6F767E] mt-1">Provide the essential information for the new user account.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="space-y-1">
                        <label class="text-[13px] font-bold text-[#6F767E] mb-2 block uppercase tracking-wider">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input name="name" value="{{ old('name') }}" 
                            class="w-full rounded-xl border-none bg-white dark:bg-[#0B0B0B] py-3 px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E] @error('name') ring-red-500 dark:ring-red-500 focus:ring-red-500 @enderror" 
                            placeholder="e.g. John Doe" type="text" required />
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[13px] font-bold text-[#6F767E] mb-2 block uppercase tracking-wider">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input name="email" value="{{ old('email') }}" 
                            class="w-full rounded-xl border-none bg-white dark:bg-[#0B0B0B] py-3 px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E] @error('email') ring-red-500 dark:ring-red-500 focus:ring-red-500 @enderror" 
                            placeholder="john.doe@company.com" type="email" required />
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[13px] font-bold text-[#6F767E] mb-2 block uppercase tracking-wider">Username</label>
                        <input name="username" value="{{ old('username') }}" 
                            class="w-full rounded-xl border-none bg-white dark:bg-[#0B0B0B] py-3 px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E] @error('username') ring-red-500 dark:ring-red-500 focus:ring-red-500 @enderror" 
                            placeholder="jdoe" type="text" />
                        @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[13px] font-bold text-[#6F767E] mb-2 block uppercase tracking-wider">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <input name="password" :type="show ? 'text' : 'password'" 
                                class="w-full rounded-xl border-none bg-white dark:bg-[#0B0B0B] py-3 px-4 pr-12 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E] @error('password') ring-red-500 dark:ring-red-500 focus:ring-red-500 @enderror" 
                                placeholder="••••••••" required />
                            <button type="button" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]">
                                <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 space-y-1">
                    <label class="text-[13px] font-bold text-[#6F767E] mb-2 block uppercase tracking-wider">Biography</label>
                    <textarea name="bio" class="w-full rounded-xl border-none bg-white dark:bg-[#0B0B0B] py-3 px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E] resize-none @error('bio') ring-red-500 dark:ring-red-500 focus:ring-red-500 @enderror"
                        placeholder="Write a short bio about the user..." rows="5">{{ old('bio') }}</textarea>
                    @error('bio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </section>
        </div>
        <div class="lg:col-span-4 space-y-8">
            <section
                class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
                <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC] mb-8">User Settings</h2>
                <div class="mb-10">
                    <label class="text-[13px] font-bold text-[#6F767E] mb-2 block uppercase tracking-wider">Profile Picture</label>
                    <div class="relative group mt-3" x-data="{ preview: null }">
                        <input type="file" name="avatar" class="hidden" id="avatar-upload" accept="image/*" @change="preview = URL.createObjectURL($event.target.files[0])">
                        <div @click="document.getElementById('avatar-upload').click()"
                            class="h-44 w-full rounded-2xl bg-gray-50 dark:bg-[#0B0B0B] flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-[#272B30] hover:border-[#2563EB]/50 transition-colors cursor-pointer overflow-hidden relative @error('avatar') border-red-500 dark:border-red-500 @enderror">
                            <template x-if="!preview">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="h-16 w-16 rounded-2xl bg-gray-100 dark:bg-[#1A1A1A] flex items-center justify-center mb-3">
                                        <span
                                            class="material-symbols-outlined text-3xl text-[#6F767E]">add_photo_alternate</span>
                                    </div>
                                    <span
                                        class="text-xs font-semibold text-[#6F767E] uppercase tracking-wider">Upload
                                        Photo</span>
                                </div>
                            </template>
                            <template x-if="preview">
                                <img :src="preview" class="w-full h-full object-cover" />
                            </template>
                        </div>
                        @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="space-y-1">
                        <label class="text-[13px] font-bold text-[#6F767E] mb-2 block uppercase tracking-wider">Role</label>
                        <div class="relative">
                            <select name="roles" class="w-full rounded-xl border-none bg-white dark:bg-[#0B0B0B] py-3 px-4 pr-10 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all appearance-none cursor-pointer @error('roles') ring-red-500 dark:ring-red-500 focus:ring-red-500 @enderror">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ (old('roles') == $role->id) || (!old('roles') && $role->name === 'Subscriber') ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <span
                                class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#6F767E]">expand_more</span>
                        </div>
                        @error('roles') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="pt-6 mt-2 border-t border-gray-100 dark:border-[#272B30]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[14px] font-bold text-[#111827] dark:text-[#FCFCFC]">Account Status</p>
                                <p class="text-xs text-[#6F767E] mt-0.5">Allow login access</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-[#0B0B0B] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-500">
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-6 mt-6 border-t border-gray-100 dark:border-[#272B30] flex items-center gap-3">
                        <a href="{{ route('admin.users.index') }}" wire:navigate
                            class="flex-1 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] px-6 py-3 text-sm font-bold text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all border border-gray-200 dark:border-[#272B30] hover:border-gray-300 dark:hover:border-[#333]">
                            Cancel
                        </a>
                        <button type="submit"
                            class="flex-1 flex items-center justify-center rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-blue-600 transition-all shadow-lg shadow-blue-500/20">
                            Create User
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>
@endsection
