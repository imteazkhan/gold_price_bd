@extends('admin.layout')
@section('title', $item->exists ? 'Edit price record' : 'Add price record')

@section('content')
  <form method="POST" action="{{ $item->exists ? route('admin.history.update', $item) : route('admin.history.store') }}" class="card p-4 sm:p-6 max-w-2xl space-y-4">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="lbl">Purity</label>
        <select name="purity_id" class="inp" required>
          @foreach($purities as $p)<option value="{{ $p->id }}" @selected(old('purity_id', $item->purity_id) == $p->id)>{{ $p->label }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="lbl">Date</label>
        <input type="date" name="recorded_on" value="{{ old('recorded_on', $item->recorded_on?->toDateString()) }}" class="inp" required>
      </div>
      <div>
        <label class="lbl">Price per gram (৳)</label>
        <input type="number" step="0.01" min="0" name="price_per_gram" value="{{ old('price_per_gram', $item->price_per_gram) }}" class="inp mono" required>
      </div>
      <div>
        <label class="lbl">Note</label>
        <input name="note" value="{{ old('note', $item->note) }}" class="inp" maxlength="255">
      </div>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('admin.history.index') }}" class="btn btn-light">Cancel</a>
    </div>
  </form>
@endsection
