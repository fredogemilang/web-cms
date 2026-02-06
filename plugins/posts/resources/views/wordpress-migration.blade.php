@extends('layouts.admin')

@section('title', 'WordPress Migration')
@section('page-title', 'Import from WordPress')

@section('content')
<div class="space-y-6">
    @livewire('plugins.wordpress-migration')
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
