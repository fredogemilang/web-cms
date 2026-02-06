@extends('layouts.admin')

@section('title', 'Add New ' . $postType->singular_label)
@section('hide-header', true)

@section('content')
    <livewire:admin.cpt.entries.entry-form :post-type="$postType" />
@endsection
