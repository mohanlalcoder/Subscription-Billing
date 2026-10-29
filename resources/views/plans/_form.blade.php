@csrf
<div class="mb-3">
    <label class="form-label" for="merchant_id">Merchant</label>
    <select id="merchant_id" name="merchant_id" class="form-select" required>
        <option value="">Choose merchant</option>
        @foreach($merchants as $merchant)
        <option value="{{ $merchant->id }}" @selected((string)old('merchant_id', $plan->merchant_id ?? '') === (string)$merchant->id)>{{ $merchant->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3"><label class="form-label" for="name">Plan name</label><input id="name" name="name" class="form-control" required maxlength="255" value="{{ old('name', $plan->name ?? '') }}"></div>
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label" for="base_price">Base price</label><input id="base_price" type="number" min="0" step="0.01" name="base_price" class="form-control" required value="{{ old('base_price', $plan->base_price ?? '0.00') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label" for="currency">Currency</label><input id="currency" name="currency" class="form-control text-uppercase" minlength="3" maxlength="3" required value="{{ old('currency', $plan->currency ?? 'INR') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label" for="billing_cycle">Billing cycle</label>
        <select id="billing_cycle" name="billing_cycle" class="form-select" required>
            <option value="monthly" @selected(old('billing_cycle', $plan->billing_cycle ?? 'monthly') === 'monthly')>Monthly</option>
            <option value="yearly" @selected(old('billing_cycle', $plan->billing_cycle ?? '') === 'yearly')>Yearly</option>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3"><label class="form-label" for="included_units">Included units</label><input id="included_units" type="number" min="0" name="included_units" class="form-control" required value="{{ old('included_units', $plan->included_units ?? 0) }}"></div>
    <div class="col-md-6 mb-3"><label class="form-label" for="overage_rate">Overage rate per unit</label><input id="overage_rate" type="number" min="0" step="0.000001" name="overage_rate" class="form-control" required value="{{ old('overage_rate', $plan->overage_rate ?? 0) }}"></div>
</div>
<div class="form-check mb-3">
    <input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked((bool)old('is_active', $plan->is_active ?? true))>
    <label for="is_active" class="form-check-label">Active plan</label>
</div>
<button class="btn btn-primary" type="submit">{{ isset($plan) ? 'Update plan' : 'Create plan' }}</button>
<a class="btn btn-secondary" href="{{ route('plans.index') }}">Cancel</a>