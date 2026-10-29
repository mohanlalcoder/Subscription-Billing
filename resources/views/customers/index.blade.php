@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Customers</h1><a class="btn btn-primary" href="{{ route('customers.create') }}">Add customer</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Merchant</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->merchant?->name ?? '—' }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('customers.edit', $customer) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                    </td>
                </tr>
                @empty<tr>
                    <td colspan="5" class="text-center py-4">No customers found.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $customers->links() }}</div>
@endsection