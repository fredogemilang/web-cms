@extends('layouts.admin')

@section('title', 'Posts Management')
@section('page-title', 'All Posts')

@section('content')
<div class="space-y-6">
    @livewire('plugins.posts-table')
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
