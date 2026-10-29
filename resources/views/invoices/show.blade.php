@extends('layouts.app')
@section('title', 'Invoice Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Invoice #{{ $invoice->id }}</h1><a href="{{ route('invoices.index') }}" class="btn btn-secondary">Back to invoices</a>
</div>
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h2 class="h6">Billing details</h2>
                <p class="mb-1"><strong>Merchant:</strong> {{ $invoice->merchant?->name ?? '—' }}</p>
                <p class="mb-1"><strong>Customer:</strong> {{ $invoice->customer?->name ?? '—' }}</p>
                <p class="mb-1"><strong>Subscription:</strong> #{{ $invoice->subscription_id }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Period:</strong> {{ $invoice->billing_period_start?->format('d M Y') }} — {{ $invoice->billing_period_end?->format('d M Y') }}</p>
                <p class="mb-1"><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
                <p class="mb-1"><strong>Issued:</strong> {{ $invoice->issued_at?->format('d M Y H:i') ?? 'Not issued' }}</p>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Unit price</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ ucfirst($item->type) }}</td>
                    <td>{{ number_format($item->quantity) }}</td>
                    <td>{{ number_format((float)$item->unit_price, 6) }}</td>
                    <td>{{ $invoice->currency }} {{ number_format((float)$item->amount, 2) }}</td>
                </tr>
                @empty<tr>
                    <td colspan="5" class="text-center py-3">No invoice items.</td>
                </tr>@endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Base amount</th>
                    <th>{{ $invoice->currency }} {{ number_format((float)$invoice->base_amount, 2) }}</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-end">Overage amount</th>
                    <th>{{ $invoice->currency }} {{ number_format((float)$invoice->overage_amount, 2) }}</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th>{{ $invoice->currency }} {{ number_format((float)$invoice->total_amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection