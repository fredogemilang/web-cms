@extends('layouts.admin')

@section('title', $postType->plural_label)
@section('page-title', $postType->plural_label)

@section('content')
    <livewire:admin.cpt.entries.entries-table :post-type="$postType" />
@endsection
