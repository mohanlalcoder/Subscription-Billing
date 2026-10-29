@extends('layouts.app')
@section('title', 'Merchants')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Merchants</h1>
    <a class="btn btn-primary" href="{{ route('merchants.create') }}">Add merchant</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($merchants as $merchant)
                <tr>
                    <td>{{ $merchant->id }}</td>
                    <td>{{ $merchant->name }}</td>
                    <td>{{ $merchant->email ?: '—' }}</td>
                    <td>{{ $merchant->created_at?->format('d M Y') }}</td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('merchants.show', $merchant) }}">View</a>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('merchants.edit', $merchant) }}">Edit</a>
                        <a class="btn btn-sm btn-outline-success" href="{{ route('merchants.dashboard', $merchant) }}">Dashboard</a>
                        <form class="d-inline" method="POST" action="{{ route('merchants.destroy', $merchant) }}" onsubmit="return confirm('Delete this merchant?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4">No merchants found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $merchants->links() }}</div>
@endsection