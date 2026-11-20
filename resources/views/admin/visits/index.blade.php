@extends('admin.layout')
@section('title', 'Visit log')

@section('content')
  <form method="GET" class="card p-4 grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
    <div class="col-span-2 md:col-span-1">
      <label class="lbl">Search (page, IP, referrer)</label>
      <input name="q" value="{{ request('q') }}" class="inp">
    </div>
    <div>
      <label class="lbl">Device</label>
      <select name="device" class="inp">
        <option value="">All</option>
        @foreach(['desktop', 'mobile', 'tablet', 'bot'] as $d)<option value="{{ $d }}" @selected(request('device') === $d)>{{ ucfirst($d) }}</option>@endforeach
      </select>
    </div>
    <div><label class="lbl">From</label><input type="date" name="from" value="{{ request('from') }}" class="inp"></div>
    <div><label class="lbl">To</label><input type="date" name="to" value="{{ request('to') }}" class="inp"></div>
    <div class="flex gap-2">
      <button class="btn btn-primary flex-1">Filter</button>
      <a href="{{ route('admin.visits.index') }}" class="btn btn-light">Reset</a>
    </div>
  </form>

  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <span class="text-xs text-gray-500">{{ number_format($items->total()) }} visit(s)</span>
      <form method="POST" action="{{ route('admin.visits.purge') }}" class="flex items-center gap-2" data-confirm="Permanently delete old visit records?">
        @csrf @method('DELETE')
        <span class="text-xs text-gray-500">Delete visits older than</span>
        <input type="number" name="days" value="180" min="1" class="inp !w-20 !py-1 text-xs">
        <span class="text-xs text-gray-500">days</span>
        <button class="btn btn-danger btn-sm">Purge</button>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Time</th><th>Page</th><th>IP</th><th>Device</th><th class="hidden md:table-cell">Browser</th><th class="hidden lg:table-cell">Referrer</th></tr></thead>
        <tbody>
          @forelse($items as $v)
            <tr>
              <td class="whitespace-nowrap text-xs">{{ $v->visited_at->format('M d, H:i:s') }}</td>
              <td class="mono text-xs max-w-[220px] truncate" title="{{ $v->full_url }}">{{ $v->path }}</td>
              <td class="mono text-xs whitespace-nowrap"><a href="{{ route('admin.visitors.show', $v->visitor_id) }}" class="text-amber-700 hover:underline">{{ $v->ip_address }}</a></td>
              <td class="capitalize text-xs">{{ $v->device_type }}</td>
              <td class="hidden md:table-cell text-xs whitespace-nowrap">{{ $v->visitor?->browser }} · {{ $v->visitor?->platform }}</td>
              <td class="hidden lg:table-cell text-xs text-gray-500 max-w-[220px] truncate" title="{{ $v->referrer }}">{{ $v->referrer ?: '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-gray-400 py-8">No visits found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  {{ $items->links() }}
@endsection
