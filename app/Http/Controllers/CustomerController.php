<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Merchant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::with('merchant')->withCount('subscriptions')->latest()->paginate(15);
        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        $merchants = Merchant::orderBy('name')->get();
        return view('customers.create', compact('merchants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = Customer::create($this->validatedData($request));
        return redirect()->route('customers.edit', $customer)->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer): View
    {
        $merchants = Merchant::orderBy('name')->get();
        return view('customers.edit', compact('customer', 'merchants'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validatedData($request, $customer));
        return redirect()->route('customers.edit', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->subscriptions()->exists() || $customer->invoices()->exists()) {
            return back()->with('error', 'Cannot delete a customer with subscriptions or invoices.');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

    private function validatedData(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'merchant_id' => ['required', 'integer', Rule::exists('merchants', 'id')],
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customer?->id),
            ],
            'company_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            // 'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
