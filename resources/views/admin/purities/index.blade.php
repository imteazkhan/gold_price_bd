@extends('admin.layout')
@section('title', 'Purities')

@section('actions')
  <a href="{{ route('admin.purities.create') }}" class="btn btn-primary btn-sm">+ Add purity</a>
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Order</th><th>Metal</th><th>Karat</th><th>Label</th><th class="hidden md:table-cell">Purity %</th><th>Shown in</th><th class="hidden sm:table-cell">Records</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
          @forelse($items as $p)
            <tr>
              <td class="mono">{{ $p->sort_order }}</td>
              <td><span class="badge {{ $p->metal === 'gold' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700' }}">{{ ucfirst($p->metal) }}</span></td>
              <td class="font-bold">{{ $p->karat }}</td>
              <td class="whitespace-nowrap">{{ $p->label }}<div class="text-[11px] text-gray-400">{{ $p->tagline }}</div></td>
              <td class="hidden md:table-cell mono">{{ $p->purity_percent ?? '—' }}</td>
              <td class="text-[11px] whitespace-nowrap">
                @if($p->is_featured)<span class="badge bg-amber-50 text-amber-700">Hero</span>@endif
                @if($p->show_in_table)<span class="badge bg-blue-50 text-blue-700">Table</span>@endif
                @if($p->show_in_calculator)<span class="badge bg-violet-50 text-violet-700">Calc</span>@endif
              </td>
              <td class="hidden sm:table-cell mono">{{ $p->histories_count }}</td>
              <td>{!! $p->is_active ? '<span class="badge bg-emerald-50 text-emerald-700">Active</span>' : '<span class="badge bg-gray-100 text-gray-500">Hidden</span>' !!}</td>
              <td class="text-right whitespace-nowrap">
                <a href="{{ route('admin.purities.edit', $p) }}" class="btn btn-light btn-sm">Edit</a>
                <form method="POST" action="{{ route('admin.purities.destroy', $p) }}" class="inline" data-confirm="Delete {{ $p->label }} and ALL its price history?">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-gray-400 py-8">No purities yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
