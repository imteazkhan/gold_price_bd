@extends('admin.layout')
@section('title', 'Price history')

@section('actions')
  <a href="{{ route('admin.history.create') }}" class="btn btn-primary btn-sm">+ Add record</a>
@endsection

@section('content')
  <form method="GET" class="card p-4 grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
    <div>
      <label class="lbl">Metal</label>
      <select name="metal" class="inp">
        <option value="">All</option>
        @foreach(['gold', 'silver'] as $m)<option value="{{ $m }}" @selected(request('metal') === $m)>{{ ucfirst($m) }}</option>@endforeach
      </select>
    </div>
    <div>
      <label class="lbl">Purity</label>
      <select name="purity_id" class="inp">
        <option value="">All</option>
        @foreach($purities as $p)<option value="{{ $p->id }}" @selected(request('purity_id') == $p->id)>{{ $p->label }}</option>@endforeach
      </select>
    </div>
    <div><label class="lbl">From</label><input type="date" name="from" value="{{ request('from') }}" class="inp"></div>
    <div><label class="lbl">To</label><input type="date" name="to" value="{{ request('to') }}" class="inp"></div>
    <div class="flex gap-2 col-span-2 md:col-span-1">
      <button class="btn btn-primary flex-1">Filter</button>
      <a href="{{ route('admin.history.index') }}" class="btn btn-light">Reset</a>
    </div>
  </form>

  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Date</th><th>Purity</th><th>Price /g</th><th class="hidden sm:table-cell">Vhori</th><th class="hidden md:table-cell">Note</th><th class="hidden lg:table-cell">Source</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
          @php $vh = (float) \App\Models\Setting::get('vhori_grams', 11.664); @endphp
          @forelse($items as $h)
            <tr>
              <td class="whitespace-nowrap">{{ $h->recorded_on->format('M d, Y') }}</td>
              <td class="whitespace-nowrap">{{ $h->purity?->label }}</td>
              <td class="mono font-bold whitespace-nowrap">{{ bdt($h->price_per_gram, 2) }}</td>
              <td class="mono hidden sm:table-cell whitespace-nowrap">{{ bdt($h->price_per_gram * $vh, 2) }}</td>
              <td class="hidden md:table-cell text-gray-500 text-xs">{{ $h->note }}</td>
              <td class="hidden lg:table-cell text-gray-500 text-xs">
                <span class="badge {{ ['auto' => 'bg-emerald-50 text-emerald-700', 'manual' => 'bg-violet-50 text-violet-700'][$h->source] ?? 'bg-gray-100 text-gray-500' }}">{{ $h->source }}</span>
                {{ $h->editor?->name }}
              </td>
              <td class="text-right whitespace-nowrap">
                <a href="{{ route('admin.history.edit', $h) }}" class="btn btn-light btn-sm">Edit</a>
                <form method="POST" action="{{ route('admin.history.destroy', $h) }}" class="inline" data-confirm="Delete this price record?">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-gray-400 py-8">No records found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  {{ $items->links() }}
@endsection
