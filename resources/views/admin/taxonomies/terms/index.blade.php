@extends('layouts.admin')

@section('title', $taxonomy->plural_label)
@section('page-title', $taxonomy->plural_label)

@section('content')
    <div class="flex flex-col lg:flex-row gap-8 items-start">
        {{-- Left Column: Quick Add --}}
        <div class="w-full lg:w-1/3 shrink-0 lg:sticky lg:top-24">
            <livewire:admin.taxonomies.terms.term-form :taxonomy="$taxonomy" :inline="true" />
        </div>

        {{-- Right Column: Term List --}}
        <div class="w-full lg:w-2/3">
             <livewire:admin.taxonomies.terms.term-table :taxonomy="$taxonomy" />
        </div>
    </div>
@endsection
