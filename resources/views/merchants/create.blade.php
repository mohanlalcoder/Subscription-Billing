@extends('layouts.app')
@section('title', 'Create Merchant')
@section('content')
<h1 class="h3 mb-4">Create merchant</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('merchants.store') }}">
            @include('merchants._form')
        </form>
    </div>
</div>
@endsection