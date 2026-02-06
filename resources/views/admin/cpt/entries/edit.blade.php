@extends('layouts.admin')

@section('title', 'Edit ' . $postType->singular_label)
@section('hide-header', true)

@section('content')
    <livewire:admin.cpt.entries.entry-form :post-type="$postType" :id="$id" />
@endsection
