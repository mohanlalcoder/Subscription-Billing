@extends('layouts.app')
@section('title', 'Plans')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Plans</h1><a class="btn btn-primary" href="{{ route('plans.create') }}">Add plan</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Merchant</th>
                    <th>Plan</th>
                    <th>Base price</th>
                    <th>Included units</th>
                    <th>Overage/unit</th>
                    <th>Cycle</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                <tr>
                    <td>{{ $plan->merchant?->name ?? '—' }}</td>
                    <td>{{ $plan->name }}</td>
                    <td>{{ $plan->currency }} {{ number_format((float)$plan->base_price, 2) }}</td>
                    <td>{{ number_format($plan->included_units) }}</td>
                    <td>{{ number_format((float)$plan->overage_rate, 6) }}</td>
                    <td>{{ ucfirst($plan->billing_cycle) }}</td>
                    <td><span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('plans.edit', $plan) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('plans.destroy', $plan) }}" onsubmit="return confirm('Delete this plan?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty<tr>
                    <td colspan="8" class="text-center py-4">No plans found.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $plans->links() }}</div>
@endsection