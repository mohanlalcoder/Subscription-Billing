<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MerchantController extends Controller
{
    public function index(): View
    {
        $merchants = Merchant::withCount(['plans', 'customers', 'subscriptions'])
            ->latest()
            ->paginate(15);

        return view('merchants.index', compact('merchants'));
    }

    public function create(): View
    {
        return view('merchants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            // 'status' => ['required', 'in:active,inactive'],
        ]);

        $merchant = Merchant::create($data);

        return redirect()->route('merchants.show', $merchant)
            ->with('success', 'Merchant created successfully.');
    }

    public function show(Merchant $merchant): View
    {
        $merchant->load(['plans', 'customers', 'subscriptions.customer', 'subscriptions.plan']);

        return view('merchants.show', compact('merchant'));
    }

    public function edit(Merchant $merchant): View
    {
        return view('merchants.edit', compact('merchant'));
    }

    public function update(Request $request, Merchant $merchant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $merchant->update($data);

        return redirect()->route('merchants.show', $merchant)
            ->with('success', 'Merchant updated successfully.');
    }

    public function destroy(Merchant $merchant): RedirectResponse
    {
        if ($merchant->subscriptions()->exists() || $merchant->customers()->exists()) {
            return back()->with('error', 'Cannot delete a merchant that has customers or subscriptions.');
        }

        $merchant->delete();

        return redirect()->route('merchants.index')->with('success', 'Merchant deleted.');
    }
}
