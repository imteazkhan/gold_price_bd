@extends('admin.layout')
@section('title', 'Visitors')

@section('content')
  <form method="GET" class="card p-4 grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
    <div class="col-span-2">
      <label class="lbl">Search (IP, user agent, referrer, ID)</label>
      <input name="q" value="{{ request('q') }}" class="inp" placeholder="e.g. 103.">
    </div>
    <div>
      <label class="lbl">Device</label>
      <select name="device" class="inp">
        <option value="">All</option>
        @foreach(['desktop', 'mobile', 'tablet', 'bot'] as $d)<option value="{{ $d }}" @selected(request('device') === $d)>{{ ucfirst($d) }}</option>@endforeach
      </select>
    </div>
    <div>
      <label class="lbl">Bots</label>
      <select name="bots" class="inp">
        <option value="">Include</option>
        <option value="exclude" @selected(request('bots') === 'exclude')>Exclude</option>
        <option value="only" @selected(request('bots') === 'only')>Only bots</option>
      </select>
    </div>
    <div><label class="lbl">Last seen from</label><input type="date" name="from" value="{{ request('from') }}" class="inp"></div>
    <div class="flex gap-2">
      <button class="btn btn-primary flex-1">Filter</button>
      <a href="{{ route('admin.visitors.index') }}" class="btn btn-light">Reset</a>
    </div>
  </form>

  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 text-xs text-gray-500">{{ number_format($items->total()) }} visitor(s)</div>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>IP</th><th>Device</th><th class="hidden sm:table-cell">Browser / OS</th><th>Visits</th><th class="hidden md:table-cell">First seen</th><th>Last seen</th><th class="hidden lg:table-cell">Came from</th><th></th></tr></thead>
        <tbody>
          @forelse($items as $v)
            <tr>
              <td class="mono text-xs whitespace-nowrap">{{ $v->ip_address }}</td>
              <td>
                <span class="badge capitalize {{ $v->is_bot ? 'bg-red-50 text-red-700' : ($v->device_type === 'mobile' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600') }}">{{ $v->device_type }}</span>
              </td>
              <td class="hidden sm:table-cell whitespace-nowrap text-xs">{{ $v->browser }} · {{ $v->platform }}</td>
              <td class="mono font-bold">{{ $v->total_visits }}</td>
              <td class="hidden md:table-cell text-xs text-gray-500 whitespace-nowrap">{{ $v->first_visit_at?->format('M d, Y H:i') }}</td>
              <td class="text-xs text-gray-600 whitespace-nowrap">{{ $v->last_visit_at?->diffForHumans() }}</td>
              <td class="hidden lg:table-cell text-xs text-gray-500 max-w-[200px] truncate" title="{{ $v->first_referrer }}">{{ $v->first_referrer ? parse_url($v->first_referrer, PHP_URL_HOST) : 'Direct' }}</td>
              <td class="text-right"><a href="{{ route('admin.visitors.show', $v) }}" class="btn btn-light btn-sm">Details</a></td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-gray-400 py-8">No visitors found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  {{ $items->links() }}
@endsection
