@csrf
@if(isset($customer))
<div class="mb-3"><label class="form-label">Merchant</label><input class="form-control" value="{{ $customer->merchant?->name }}" disabled>
    <input type="hidden" class="form-control" name="merchant_id" value="{{ $customer->merchant?->id }}">
    <div class="form-text">Customer tenant cannot be changed here.</div>
</div>
@else
<div class="mb-3"><label for="merchant_id" class="form-label">Merchant</label><select id="merchant_id" name="merchant_id" class="form-select" required>
        <option value="">Choose merchant</option>@foreach($merchants as $merchant)<option value="{{ $merchant->id }}" @selected((string)old('merchant_id')===(string)$merchant->id)>{{ $merchant->name }}</option>@endforeach
    </select></div>
@endif
<div class="mb-3"><label for="name" class="form-label">Customer name</label><input id="name" name="name" class="form-control" maxlength="255" required value="{{ old('name', $customer->name ?? '') }}"></div>
<div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" type="email" name="email" class="form-control" maxlength="255" required value="{{ old('email', $customer->email ?? '') }}"></div>
<button class="btn btn-primary">{{ isset($customer) ? 'Update customer' : 'Create customer' }}</button><a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>