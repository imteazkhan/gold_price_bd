@extends('admin.layout')
@section('title', $item->exists ? 'Edit '.$item->country : 'Add country price')

@section('content')
  <form method="POST" action="{{ $item->exists ? route('admin.countries.update', $item) : route('admin.countries.store') }}" class="card p-4 sm:p-6 max-w-3xl space-y-4">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="sm:col-span-2">
        <label class="lbl">Country</label>
        <input name="country" value="{{ old('country', $item->country) }}" class="inp" required>
      </div>
      <div>
        <label class="lbl">Flag emoji</label>
        <input name="flag" value="{{ old('flag', $item->flag) }}" class="inp" maxlength="16" placeholder="🇦🇪">
      </div>
      <div>
        <label class="lbl">Karat</label>
        <input name="karat" list="karatList" value="{{ old('karat', $item->karat) }}" class="inp" required>
        <datalist id="karatList">@foreach($karats as $k)<option value="{{ $k }}">@endforeach</datalist>
      </div>
      <div>
        <label class="lbl">Currency code</label>
        <input name="currency" value="{{ old('currency', $item->currency) }}" class="inp uppercase" maxlength="10" placeholder="AED" required>
      </div>
      <div>
        <label class="lbl">Sort order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="inp">
      </div>
      <div>
        <label class="lbl">Local price per gram</label>
        <input type="number" step="0.01" min="0" name="local_price" id="lp" value="{{ old('local_price', $item->local_price) }}" class="inp mono" required>
      </div>
      <div>
        <label class="lbl">1 unit = ? BDT</label>
        <input type="number" step="0.0001" min="0" name="fx_to_bdt" id="fx" value="{{ old('fx_to_bdt', $item->fx_to_bdt) }}" class="inp mono" required>
      </div>
      <div>
        <label class="lbl">= BDT per gram</label>
        <input id="bdt" class="inp mono bg-gray-50" readonly>
      </div>
    </div>
    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active)) class="rounded text-amber-500"> Active (show on website)
    </label>
    <div class="flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('admin.countries.index') }}" class="btn btn-light">Cancel</a>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  const lp = document.getElementById('lp'), fx = document.getElementById('fx'), out = document.getElementById('bdt');
  const upd = () => out.value = '৳' + ((parseFloat(lp.value) || 0) * (parseFloat(fx.value) || 0)).toLocaleString('en-US', { maximumFractionDigits: 2 });
  lp.addEventListener('input', upd); fx.addEventListener('input', upd); upd();
</script>
@endpush
