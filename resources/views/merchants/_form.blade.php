@csrf
<div class="mb-3">
    <label for="name" class="form-label">Merchant name</label>
    <input id="name" name="name" class="form-control" maxlength="255" required value="{{ old('name', $merchant->name ?? '') }}">
</div>
<div class="mb-3">
    <label for="email" class="form-label">Email (optional)</label>
    <input id="email" type="email" name="email" class="form-control" maxlength="255" value="{{ old('email', $merchant->email ?? '') }}">
</div>
<button class="btn btn-primary" type="submit">{{ isset($merchant) ? 'Update merchant' : 'Create merchant' }}</button>
<a class="btn btn-secondary" href="{{ route('merchants.index') }}">Cancel</a>