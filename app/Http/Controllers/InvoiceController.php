<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        $invoices = Invoice::with(['merchant', 'customer', 'subscription'])
            ->latest('issued_at')
            ->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['merchant', 'customer', 'subscription.plan', 'items']);
        return view('invoices.show', compact('invoice'));
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'paid') {
            return back()->with('info', 'Invoice is already paid.');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice marked as paid.');
    }
}
