@extends('layouts.app')
@section('title', 'Merchant Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $merchant->name }} dashboard</h1>
        <div class="text-muted">Usage and billing overview</div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('merchants.index') }}">All merchants</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Projected overage revenue</div>
                <div class="fs-3 fw-semibold">{{ $merchant->plans()->first()?->currency ?? 'INR' }} {{ number_format((float)($projectedOverageRevenue ?? 0), 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Customers with subscriptions</div>
                <div class="fs-3 fw-semibold">{{ $activeSubscriptionCustomers ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Churn-risk customers</div>
                <div class="fs-3 fw-semibold">{{ isset($churnRiskCustomers) ? $churnRiskCustomers->count() : 0 }}</div>
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header bg-white">
        <h2 class="h5 mb-0">Top 5 customers by usage this month</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Total usage</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($topCustomers ?? collect()) as $row)
                <tr>
                    <td>{{ $row->name ?? $row->customer?->name ?? 'Customer' }}</td>
                    <td>{{ number_format($row->total_usage ?? 0) }}</td>
                </tr>
                @empty<tr>
                    <td colspan="2" class="text-center py-3">No usage data for this month.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>


<div class="card">
    <div class="card-header bg-white">
        <h2 class="h5 mb-1">Churn-risk customers</h2>
        <div class="text-muted small">
            {{ now()->subMonthNoOverflow()->format('F Y') }}
            vs
            {{ now()->format('F Y') }}
            — usage dropped by more than 50%
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Previous usage</th>
                    <th>Current usage</th>
                    <th>Usage drop</th>
                </tr>
            </thead>

            <tbody>
                @forelse($churnRiskCustomers as $row)
                    <tr>
                        <td>{{ $row->customer_id }}</td>

                        <td>
                            {{ $row->name }}
                        </td>

                        <td>
                            {{ $row->email ?: '—' }}
                        </td>

                        <td>
                            {{ number_format($row->previous_usage, 0) }}
                        </td>

                        <td>
                            {{ number_format($row->current_usage, 0) }}
                        </td>

                        <td>
                            <span class="badge bg-danger">
                                {{ number_format($row->drop_percent, 1) }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            No churn-risk customers identified for
                            {{ now()->subMonthNoOverflow()->format('F') }}
                            and {{ now()->format('F') }}.
                            <div class="text-muted small mt-1">
                                A customer must have previous-month usage
                                and a drop greater than 50%.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection