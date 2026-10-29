@extends('layouts.app')
@section('title', 'Edit Plan')
@section('content')
<h1 class="h3 mb-4">Edit plan</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('plans.update', $plan) }}">@method('PUT') @include('plans._form')</form>
    </div>
</div>
@endsection