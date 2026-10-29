@extends('layouts.app')
@section('title', 'Merchant Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ $merchant->name }}</h1>
    <div>
        <a class="btn btn-outline-success" href="{{ route('merchants.dashboard', $merchant) }}">Dashboard</a>
        <a class="btn btn-outline-secondary" href="{{ route('merchants.edit', $merchant) }}">Edit</a>
        <a class="btn btn-secondary" href="{{ route('merchants.index') }}">Back</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9">{{ $merchant->id }}</dd>
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $merchant->name }}</dd>
            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9">{{ $merchant->email ?: '—' }}</dd>
            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9">{{ $merchant->created_at?->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection