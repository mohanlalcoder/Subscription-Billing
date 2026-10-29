@extends('layouts.app')
@section('title', 'Usage Events')
@section('content')
<h1 class="h3 mb-4">Usage events</h1>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Merchant</th>
                    <th>Customer</th>
                    <th>Plan</th>
                    <th>Idempotency key</th>
                    <th>Quantity</th>
                    <th>Occurred at</th>
                    <th>Aggregated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usageEvents as $event)
                <tr>
                    <td>{{ $event->id }}</td>
                    <td>{{ $event->merchant?->name ?? '—' }}</td>
                    <td>{{ $event->customer?->name ?? '—' }}</td>
                    <td>{{ $event->subscriptionPeriod?->subscription?->plan?->name ?? '—' }}</td>
                    <td><code>{{ $event->idempotency_key }}</code></td>
                    <td>{{ number_format($event->quantity) }}</td>
                    <td>{{ $event->occurred_at?->format('d M Y H:i:s') }}</td>
                    <td>{{ $event->aggregated_at ? 'Yes' : 'Pending' }}</td>
                </tr>
                @empty<tr>
                    <td colspan="8" class="text-center py-4">No usage events found.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $usageEvents->links() }}</div>
@endsection