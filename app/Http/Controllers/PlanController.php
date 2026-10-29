<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::with('merchant')->latest()->paginate(15);
        return view('plans.index', compact('plans'));
    }

    public function create(): View
    {
        $merchants = Merchant::orderBy('name')->get();
        return view('plans.create', compact('merchants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $plan = Plan::create($data);

        return redirect()->route('plans.edit', $plan)->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan): View
    {
        $merchants = Merchant::orderBy('name')->get();
        return view('plans.edit', compact('plan', 'merchants'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validatedData($request, $plan));
        return redirect()->route('plans.edit', $plan)->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'Cannot delete a plan that is used by subscriptions.');
        }

        $plan->delete();
        return redirect()->route('plans.index')->with('success', 'Plan deleted.');
    }

    private function validatedData(Request $request, ?Plan $plan = null): array {
        $data = $request->validate([
            'merchant_id' => [
                'required',
                'integer',
                Rule::exists('merchants', 'id'),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('plans', 'name')
                    ->where('merchant_id', $request->input('merchant_id'))
                    ->ignore($plan?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'included_units' => ['required', 'integer', 'min:0'],
            'overage_rate' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
