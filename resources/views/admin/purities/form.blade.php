@extends('admin.layout')
@section('title', $item->exists ? 'Edit '.$item->label : 'Add purity')

@section('content')
  <form method="POST" action="{{ $item->exists ? route('admin.purities.update', $item) : route('admin.purities.store') }}" class="card p-4 sm:p-6 max-w-3xl space-y-4">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="lbl">Metal</label>
        <select name="metal" class="inp" required>
          @foreach(['gold', 'silver'] as $m)<option value="{{ $m }}" @selected(old('metal', $item->metal) === $m)>{{ ucfirst($m) }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="lbl">Karat (e.g. 22K, Traditional)</label>
        <input name="karat" value="{{ old('karat', $item->karat) }}" class="inp" maxlength="30" required>
      </div>
      <div>
        <label class="lbl">Display label</label>
        <input name="label" value="{{ old('label', $item->label) }}" class="inp" placeholder="22K Gold" required>
      </div>
      <div>
        <label class="lbl">Purity % (optional)</label>
        <input type="number" step="0.01" min="0" max="100" name="purity_percent" value="{{ old('purity_percent', $item->purity_percent) }}" class="inp">
      </div>
      <div>
        <label class="lbl">Tagline</label>
        <input name="tagline" value="{{ old('tagline', $item->tagline) }}" class="inp" placeholder="Most popular">
      </div>
      <div>
        <label class="lbl">Sort order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="inp">
      </div>
    </div>
    <div>
      <label class="lbl">Karat guide description (shown in the "Gold karat guide" section)</label>
      <textarea name="description" rows="3" class="inp">{{ old('description', $item->description) }}</textarea>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      @foreach(['is_active' => 'Active', 'is_featured' => 'Hero card', 'show_in_table' => 'Rate table', 'show_in_calculator' => 'Calculator'] as $f => $l)
        <label class="flex items-center gap-2 text-sm border border-gray-200 rounded-lg px-3 py-2">
          <input type="checkbox" name="{{ $f }}" value="1" @checked(old($f, $item->$f)) class="rounded text-amber-500"> {{ $l }}
        </label>
      @endforeach
    </div>
    <p class="text-[11px] text-gray-500">The calculator only lists gold purities. Up to 3 hero cards are shown.</p>
    <div class="flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('admin.purities.index') }}" class="btn btn-light">Cancel</a>
    </div>
  </form>
@endsection
