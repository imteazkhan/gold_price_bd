@extends('admin.layout')
@section('title', 'Dashboard')

@section('actions')
  <a href="{{ route('admin.rates.index') }}" class="btn btn-primary btn-sm">Update today's rates</a>
@endsection

@section('content')
  {{-- Stat cards --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    @foreach([
      ['Visitors today', $stats['visitors_today'], $stats['new_today'].' new', 'text-amber-600'],
      ['Page views today', $stats['visits_today'], 'all visits', 'text-blue-600'],
      ['Total visitors', $stats['visitors_total'], $stats['bots_total'].' bots excluded', 'text-emerald-600'],
      ['Total page views', $stats['visits_total'], $stats['estimates_total'].' saved estimates', 'text-violet-600'],
    ] as [$label, $value, $sub, $color])
      <div class="card p-4">
        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ $label }}</p>
        <p class="text-2xl sm:text-3xl font-extrabold mono mt-1 {{ $color }}">{{ number_format($value) }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">{{ $sub }}</p>
      </div>
    @endforeach
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="card p-4 sm:p-5 xl:col-span-2">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-bold text-gray-900 text-sm">Traffic · last 14 days</h2>
        <div class="flex gap-3 text-[11px] text-gray-500">
          <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span>Page views</span>
          <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-slate-500"></span>Visitors</span>
        </div>
      </div>
      <div class="relative h-60"><canvas id="trafficChart"></canvas></div>
    </div>

    <div class="card p-4 sm:p-5">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-bold text-gray-900 text-sm">Current rates</h2>
        <span class="text-[11px] text-gray-400">{{ $lastUpdated?->diffForHumans() ?? '—' }}</span>
      </div>
      <div class="divide-y divide-gray-100">
        @foreach($rates as $r)
          <div class="flex items-center justify-between py-2 text-sm">
            <span class="text-gray-700">{{ $r->label }}</span>
            <span class="flex items-center gap-2">
              <span class="mono font-bold">{{ bdt($r->price) }}</span>
              <span class="text-[11px] font-semibold w-16 text-right {{ $r->change > 0 ? 'text-green-600' : ($r->change < 0 ? 'text-red-600' : 'text-gray-400') }}">{{ signed_bdt($r->change) }}</span>
            </span>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    @foreach([
      ['Devices', $devices],
      ['Browsers', $browsers],
    ] as [$title, $data])
      @php $sum = max(1, $data->sum()); @endphp
      <div class="card p-4 sm:p-5">
        <h2 class="font-bold text-gray-900 text-sm mb-3">{{ $title }}</h2>
        @forelse($data as $name => $count)
          <div class="mb-2.5">
            <div class="flex justify-between text-xs mb-1"><span class="capitalize text-gray-700">{{ $name ?: 'unknown' }}</span><span class="mono text-gray-500">{{ $count }} · {{ round($count / $sum * 100) }}%</span></div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-amber-500 rounded-full" style="width: {{ $count / $sum * 100 }}%"></div></div>
          </div>
        @empty
          <p class="text-xs text-gray-400">No data yet.</p>
        @endforelse
      </div>
    @endforeach

    <div class="card p-4 sm:p-5">
      <h2 class="font-bold text-gray-900 text-sm mb-3">Top pages</h2>
      @forelse($topPages as $p)
        <div class="flex justify-between gap-2 text-xs py-1.5 border-b border-gray-50 last:border-0"><span class="truncate text-gray-700 mono">{{ $p->path }}</span><span class="mono text-gray-500">{{ $p->total }}</span></div>
      @empty
        <p class="text-xs text-gray-400">No data yet.</p>
      @endforelse
    </div>

    <div class="card p-4 sm:p-5">
      <h2 class="font-bold text-gray-900 text-sm mb-3">Top referrers</h2>
      @forelse($topReferrers as $r)
        <div class="flex justify-between gap-2 text-xs py-1.5 border-b border-gray-50 last:border-0"><span class="truncate text-gray-700" title="{{ $r->first_referrer }}">{{ parse_url($r->first_referrer, PHP_URL_HOST) ?: $r->first_referrer }}</span><span class="mono text-gray-500">{{ $r->total }}</span></div>
      @empty
        <p class="text-xs text-gray-400">Direct traffic only so far.</p>
      @endforelse
    </div>
  </div>

  <div class="card overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b border-gray-100">
      <h2 class="font-bold text-gray-900 text-sm">Recent visits</h2>
      <a href="{{ route('admin.visits.index') }}" class="text-xs font-semibold text-amber-600 hover:underline">View all</a>
    </div>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Time</th><th>Page</th><th>IP</th><th>Device</th><th class="hidden md:table-cell">Browser</th></tr></thead>
        <tbody>
          @forelse($recentVisits as $v)
            <tr>
              <td class="whitespace-nowrap text-gray-600">{{ $v->visited_at->diffForHumans() }}</td>
              <td class="mono text-xs">{{ $v->path }}</td>
              <td class="mono text-xs whitespace-nowrap"><a class="text-amber-700 hover:underline" href="{{ route('admin.visitors.show', $v->visitor_id) }}">{{ $v->ip_address }}</a></td>
              <td class="capitalize">{{ $v->device_type }}</td>
              <td class="hidden md:table-cell">{{ $v->visitor?->browser }} · {{ $v->visitor?->platform }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-gray-400 py-6">No visits yet. Open the website to record one.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  const traffic = @json($chart);
  new Chart(document.getElementById('trafficChart'), {
    type: 'bar',
    data: {
      labels: traffic.map(d => d.label),
      datasets: [
        { label: 'Page views', data: traffic.map(d => d.visits), backgroundColor: '#f59e0b', borderRadius: 4 },
        { label: 'Visitors', data: traffic.map(d => d.visitors), backgroundColor: '#64748b', borderRadius: 4 },
      ],
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, font: { size: 10 } } } },
    },
  });
</script>
@endpush
