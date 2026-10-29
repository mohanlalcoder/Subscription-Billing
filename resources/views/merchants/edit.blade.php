@extends('layouts.app')
@section('title', 'Edit Merchant')
@section('content')
<h1 class="h3 mb-4">Edit merchant</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('merchants.update', $merchant) }}">
            @method('PUT')
            @include('merchants._form')
        </form>
    </div>
</div>
@endsection