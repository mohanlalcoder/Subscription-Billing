@extends('layouts.app')
@section('title', 'Subscriptions')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Subscriptions</h1><a class="btn btn-primary" href="{{ route('subscriptions.create') }}">Create subscription</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Merchant</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Period start</th>
                    <th>Period end</th>
                    <th>Plan history</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                <tr>
                    <td>{{ $subscription->id }}</td>
                    <td>{{ $subscription->merchant?->name ?? '—' }}</td>
                    <td>{{ $subscription->customer?->name ?? '—' }}</td>
                    <td><span class="badge {{ $subscription->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($subscription->status) }}</span></td>
                    <td>{{ $subscription->starts_at?->format('d M Y') }}</td>
                    <td>{{ $subscription->ends_at?->format('d M Y') }}</td>
                    <td>{{ $subscription->plan?->name ?? 'Plan deleted' }}
                        @forelse($subscription->periods as $period) ({{ $period->starts_at?->format('d M Y') }} — {{ $period->ends_at?->format('d M Y') ?? 'ongoing' }})</div>@empty — @endforelse</td>
                </tr>
                @empty<tr>
                    <td colspan="7" class="text-center py-4">No subscriptions found.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $subscriptions->links() }}</div>
@endsection