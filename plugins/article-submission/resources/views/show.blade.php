@extends('layouts.admin')

@section('title', 'Submission Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.article-submissions.index') }}" 
           class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-[#272B30] flex items-center justify-center hover:bg-gray-200 dark:hover:bg-[#333] transition-colors">
            <span class="material-symbols-outlined text-[#6F767E]">arrow_back</span>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-[#111827] dark:text-[#FCFCFC]">Submission Details</h1>
            <p class="text-[#6F767E] mt-1">View article submission from {{ $submission->name }}</p>
        </div>
    </div>

    <!-- Status Badge -->
    @php
        $statusClasses = [
            'pending' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
            'reviewed' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
            'approved' => 'bg-[#3F8C5826] text-[#83BF6E]',
            'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
        ];
    @endphp

    <!-- Submission Details Card -->
    <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden">
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <span class="inline-flex items-center gap-1.5 rounded-lg {{ $statusClasses[$submission->status] ?? $statusClasses['pending'] }} px-3 py-1.5 text-sm font-bold uppercase tracking-wider">
                    <span class="h-2 w-2 rounded-full bg-current"></span>
                    {{ ucfirst($submission->status) }}
                </span>
                <span class="text-sm text-[#6F767E]">Submitted {{ $submission->created_at->format('M d, Y \a\t H:i') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Name</label>
                    <p class="text-lg font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $submission->name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Email</label>
                    <a href="mailto:{{ $submission->email }}" class="text-lg font-medium text-[#2563EB] hover:underline">{{ $submission->email }}</a>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Phone</label>
                    <p class="text-lg font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $submission->phone ?? '-' }}</p>
                </div>

                <!-- Job Level -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Job Level</label>
                    <p class="text-lg font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $submission->job_level ?? '-' }}</p>
                </div>

                <!-- Job Title -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Job Title</label>
                    <p class="text-lg font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $submission->job_title ?? '-' }}</p>
                </div>

                <!-- Domicile -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Domicile</label>
                    <p class="text-lg font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $submission->domicile ?? '-' }}</p>
                </div>

                <!-- LinkedIn -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">LinkedIn</label>
                    @if($submission->linkedin)
                        <a href="{{ $submission->linkedin }}" target="_blank" class="text-lg font-medium text-[#2563EB] hover:underline">{{ $submission->linkedin }}</a>
                    @else
                        <p class="text-lg font-medium text-[#111827] dark:text-[#FCFCFC]">-</p>
                    @endif
                </div>

                <!-- Institution -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Institution/Company</label>
                    <p class="text-lg font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $submission->institution ?? '-' }}</p>
                </div>

                <!-- Education Level -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Education Level</label>
                    <p class="text-lg font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $submission->education_level ?? '-' }}</p>
                </div>

                <!-- Industry -->
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Industry</label>
                    <p class="text-lg font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $submission->industry ?? '-' }}</p>
                </div>

                <!-- Article File -->
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Article File</label>
                    @if($submission->article_file)
                        <a href="{{ route('admin.article-submissions.download', $submission->id) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-[#2563EB] hover:bg-blue-600 text-white rounded-xl font-medium transition-colors">
                            <span class="material-symbols-outlined text-lg">download</span>
                            Download Article (PDF)
                        </a>
                    @else
                        <p class="text-lg font-medium text-[#6F767E]">No file uploaded</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
