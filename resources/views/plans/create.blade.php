@extends('layouts.app')
@section('title', 'Create Plan')
@section('content')
<h1 class="h3 mb-4">Create plan</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('plans.store') }}">@include('plans._form')</form>
    </div>
</div>
@endsection