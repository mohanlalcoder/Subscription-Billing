@extends('layouts.app')
@section('title', 'Invoices')
@section('content')
<h1 class="h3 mb-4">Invoices</h1>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Merchant</th>
                    <th>Customer</th>
                    <th>Billing period</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Issued</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->id }}</td>
                    <td>{{ $invoice->merchant?->name ?? '—' }}</td>
                    <td>{{ $invoice->customer?->name ?? '—' }}</td>
                    <td>{{ $invoice->billing_period_start?->format('d M Y') }} — {{ $invoice->billing_period_end?->format('d M Y') }}</td>
                    <td>{{ $invoice->currency }} {{ number_format((float)$invoice->total_amount, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span></td>
                    <td>{{ $invoice->issued_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('invoices.show', $invoice) }}">Details</a></td>
                </tr>
                @empty<tr>
                    <td colspan="8" class="text-center py-4">No invoices found yet.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $invoices->links() }}</div>
@endsection