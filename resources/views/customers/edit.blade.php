@extends('layouts.app')
@section('title', 'Edit Customer')
@section('content')
<h1 class="h3 mb-4">Edit customer</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('customers.update', $customer) }}">@method('PUT') @include('customers._form')</form>
    </div>
</div>
@endsection