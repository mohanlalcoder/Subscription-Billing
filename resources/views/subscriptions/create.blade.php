@extends('layouts.app')
@section('title', 'Create Subscription')
@section('content')
<h1 class="h3 mb-4">Create subscription</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('subscriptions.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="merchant_id">Merchant</label>
                <select id="merchant_id" name="merchant_id" class="form-select" required>
                    <option value="">Choose merchant</option>
                    @foreach($merchants as $merchant)
                        <option value="{{ $merchant->id }}"
                            @selected((string) old('merchant_id') === (string) $merchant->id)>
                            {{ $merchant->name }}
                        </option>
                    @endforeach
                </select>
                @error('merchant_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id" class="form-select" required>
                    <option value="">Choose customer</option>

                    @foreach($customers as $customer)
                        <option
                            data-id="{{ $customer->merchant_id }}"
                            value="{{ $customer->id }}"
                            @selected((string) old('customer_id') === (string) $customer->id)>
                            {{ $customer->name }} — {{ $customer->merchant?->name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="plan_id">Plan</label>
                <select id="plan_id" name="plan_id" class="form-select" required>
                    <option value="">Choose plan</option>

                    @foreach($plans as $plan)
                        <option
                            data-id="{{ $plan->merchant_id }}"
                            value="{{ $plan->id }}"
                            @selected((string) old('plan_id') === (string) $plan->id)>
                            {{ $plan->name }} — {{ $plan->merchant?->name }}
                            ({{ $plan->currency }} {{ $plan->base_price }})
                        </option>
                    @endforeach
                </select>
                @error('plan_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <!-- <div class="mb-3"><label class="form-label" for="customer_id">Customer</label><select id="customer_id" name="customer_id" class="form-select" required>
                    <option value="">Choose customer</option>@foreach($customers as $customer)<option data-id="{{ $customer->merchant?->id }}" value="{{ $customer->id }}" @selected((string)old('customer_id')===(string)$customer->id)>{{ $customer->name }} — {{ $customer->merchant?->name }}</option>@endforeach
                </select></div>
            <div class="mb-3"><label class="form-label" for="plan_id">Plan</label><select id="plan_id" name="plan_id" class="form-select" required>
                    <option value="">Choose plan</option>@foreach($plans as $plan)<option data-id="{{ $plan->merchant?->id }}" value="{{ $plan->id }}" @selected((string)old('plan_id')===(string)$plan->id)>{{ $plan->name }} — {{ $plan->merchant?->name }} ({{ $plan->currency }} {{ $plan->base_price }})</option>@endforeach
                </select></div> -->
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label" for="starts_at">Period starts at</label><input id="starts_at" type="datetime-local" name="starts_at" class="form-control" required value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}"></div>
                <div class="col-md-6 mb-3"><label class="form-label" for="ends_at">Period ends at</label><input id="ends_at" type="datetime-local" name="ends_at" class="form-control" required value="{{ old('ends_at', now()->addMonth()->format('Y-m-d\TH:i')) }}"></div>
            </div> 
            <button class="btn btn-primary">Create subscription</button><a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const merchantSelect = document.getElementById('merchant_id');
    const customerSelect = document.getElementById('customer_id');
    const planSelect = document.getElementById('plan_id');

    function filterOptions(select) {
        const merchantId = merchantSelect.value;

        Array.from(select.options).forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = !merchantId || option.dataset.id !== merchantId;
        });

        // Clear a selection that doesn't belong to the selected merchant.
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption && selectedOption.hidden) {
            select.value = '';
        }
    }

    function filterAll() {
        filterOptions(customerSelect);
        filterOptions(planSelect);
    }

    merchantSelect.addEventListener('change', filterAll);

    filterAll();
});
</script>