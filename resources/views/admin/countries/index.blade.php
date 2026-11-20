@extends('admin.layout')
@section('title', 'Country prices')

@section('actions')
  <a href="{{ route('admin.countries.create') }}" class="btn btn-primary btn-sm">+ Add country</a>
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Karat</th><th>Country</th><th>Local /g</th><th class="hidden sm:table-cell">FX → BDT</th><th>BDT /g</th><th class="hidden md:table-cell">Order</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
          @forelse($items as $c)
            <tr>
              <td class="font-bold">{{ $c->karat }}</td>
              <td class="whitespace-nowrap">{{ $c->flag }} {{ $c->country }}</td>
              <td class="mono whitespace-nowrap">{{ number_format($c->local_price, 2) }} {{ $c->currency }}</td>
              <td class="mono hidden sm:table-cell">{{ rtrim(rtrim($c->fx_to_bdt, '0'), '.') }}</td>
              <td class="mono font-bold">{{ bdt($c->bdt_per_gram) }}</td>
              <td class="mono hidden md:table-cell">{{ $c->sort_order }}</td>
              <td>{!! $c->is_active ? '<span class="badge bg-emerald-50 text-emerald-700">Active</span>' : '<span class="badge bg-gray-100 text-gray-500">Hidden</span>' !!}</td>
              <td class="text-right whitespace-nowrap">
                <a href="{{ route('admin.countries.edit', $c) }}" class="btn btn-light btn-sm">Edit</a>
                <form method="POST" action="{{ route('admin.countries.destroy', $c) }}" class="inline" data-confirm="Delete this row?">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-gray-400 py-8">No country prices yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
