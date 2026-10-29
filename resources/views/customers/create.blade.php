@extends('layouts.app')
@section('title', 'Create Customer')
@section('content')
<h1 class="h3 mb-4">Create customer</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('customers.store') }}">@include('customers._form')</form>
    </div>
</div>
@endsection