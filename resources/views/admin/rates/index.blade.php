@extends('admin.layout')
@section('title', 'Update rates')

@section('actions')
  <form method="POST" action="{{ route('admin.rates.fetch') }}">
    @csrf
    <button class="btn btn-primary btn-sm">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
      Fetch live now
    </button>
  </form>
@endsection

@section('content')
  @php
    $last = $sync->lastFetchAt();
    $status = $settings['auto_last_status'];
    $statusColor = ['success' => 'bg-emerald-50 text-emerald-700', 'unchanged' => 'bg-gray-100 text-gray-600', 'skipped' => 'bg-amber-50 text-amber-700', 'failed' => 'bg-red-50 text-red-700'];
    $isToday = $date === now()->toDateString();
  @endphp

  {{-- ===== Auto update (live) ===== --}}
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="relative flex h-2.5 w-2.5">
          @if($sync->enabled())<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>@endif
          <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $sync->enabled() ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
        </span>
        <h2 class="font-bold text-sm">Live auto update · goldr.org</h2>
        <span class="badge {{ $sync->enabled() ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $sync->enabled() ? 'ON' : 'OFF' }}</span>
      </div>
      <div class="text-xs text-gray-500">
        Last fetch: <b class="text-gray-700">{{ $last ? $last->diffForHumans() : 'never' }}</b>
        @if($status)<span class="badge {{ $statusColor[$status] ?? 'bg-gray-100' }} ml-1">{{ $status }}</span>@endif
        @if($sync->enabled() && $last) · next ≈ {{ $last->copy()->addMinutes($sync->intervalMinutes())->diffForHumans() }} @endif
      </div>
    </div>
    @if($settings['auto_last_message'])
      <p class="px-4 pt-3 text-xs text-gray-600">{{ $settings['auto_last_message'] }}</p>
    @endif

    <form method="POST" action="{{ route('admin.rates.auto') }}" class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      @csrf @method('PUT')
      <div class="space-y-2">
        <label class="flex items-start gap-2 text-sm">
          <input type="checkbox" name="auto_update_enabled" value="1" @checked($settings['auto_update_enabled']) class="mt-0.5 rounded text-amber-500">
          <span><b>Auto update prices</b><span class="block text-[11px] text-gray-500">Fetch 22K, 21K, 18K, Traditional gold &amp; silver automatically.</span></span>
        </label>
        <label class="flex items-start gap-2 text-sm">
          <input type="checkbox" name="auto_update_override_manual" value="1" @checked($settings['auto_update_override_manual']) class="mt-0.5 rounded text-amber-500">
          <span><b>Live price overrides my manual price</b><span class="block text-[11px] text-gray-500">Off = a price you saved manually for today is kept until tomorrow.</span></span>
        </label>
        <label class="flex items-start gap-2 text-sm">
          <input type="checkbox" name="auto_update_usd" value="1" @checked($settings['auto_update_usd']) class="mt-0.5 rounded text-amber-500">
          <span><b>Also update USD/BDT</b><span class="block text-[11px] text-gray-500">Top-bar exchange rate from the same source.</span></span>
        </label>
      </div>
      <div class="grid grid-cols-3 gap-3 content-start">
        <div>
          <label class="lbl">Fetch every (min)</label>
          <input type="number" name="auto_update_interval" min="5" max="1440" value="{{ $settings['auto_update_interval'] }}" class="inp mono">
        </div>
        <div>
          <label class="lbl">Reject jump &gt; %</label>
          <input type="number" step="0.5" name="auto_update_max_jump" min="1" max="100" value="{{ $settings['auto_update_max_jump'] }}" class="inp mono">
        </div>
        <div>
          <label class="lbl">Page refresh (sec)</label>
          <input type="number" name="live_refresh_seconds" min="30" max="3600" value="{{ $settings['live_refresh_seconds'] }}" class="inp mono">
        </div>
        <div class="col-span-3">
          <button class="btn btn-light w-full sm:w-auto">Save auto settings</button>
        </div>
      </div>
    </form>
    <p class="px-4 pb-3 text-[11px] text-gray-400">24K is not provided by the live source — keep it updated manually below.</p>
  </div>

  {{-- ===== Manual entry ===== --}}
  <form method="GET" class="card p-4 flex flex-col sm:flex-row sm:items-end gap-3">
    <div>
      <label class="lbl" for="date">Rate date</label>
      <input id="date" type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" class="inp sm:w-48" onchange="this.form.submit()">
    </div>
    <p class="text-xs text-gray-500 sm:pb-2">Manual update: change a price and save. Only changed rows are marked <span class="badge bg-violet-50 text-violet-700">manual</span>. Leave blank to skip.</p>
  </form>

  <form method="POST" action="{{ route('admin.rates.store') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="recorded_on" value="{{ $date }}">

    @foreach($rates->groupBy('metal') as $metal => $list)
      <div class="card overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h2 class="font-bold text-sm">{{ ucfirst($metal) }}</h2>
          <span class="text-[11px] text-gray-400">Price per gram (BDT)</span>
        </div>
        <div class="divide-y divide-gray-100">
          @foreach($list as $r)
            @php
              $val = old("prices.{$r->id}", $existing[$r->id] ?? '');
              $src = $sources[$r->id] ?? null;
              $srcColor = ['auto' => 'bg-emerald-50 text-emerald-700', 'manual' => 'bg-violet-50 text-violet-700', 'seed' => 'bg-gray-100 text-gray-500'];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-center px-4 py-3" data-rate-row>
              <div class="sm:col-span-3">
                <p class="font-semibold text-sm flex items-center gap-1.5 flex-wrap">
                  {{ $r->label }}
                  @if($src)<span class="badge {{ $srcColor[$src] ?? 'bg-gray-100' }}">{{ $src }}</span>@endif
                </p>
                <p class="text-[11px] text-gray-500">Latest: <span class="mono">{{ bdt($r->price) }}</span> @if($r->recorded_on) on {{ $r->recorded_on->format('M d') }} @endif</p>
              </div>
              <div class="sm:col-span-4">
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">৳</span>
                  <input type="number" step="0.01" min="0" name="prices[{{ $r->id }}]" value="{{ $val }}" placeholder="{{ $r->price ?: '' }}"
                         class="inp pl-7 mono font-bold" data-price data-latest="{{ $r->price }}">
                </div>
              </div>
              <div class="sm:col-span-5 flex items-center justify-between sm:justify-start gap-4 text-xs text-gray-500">
                <span>Vhori: <span class="mono font-semibold text-gray-700" data-vhori>—</span></span>
                <span>Change: <span class="mono font-semibold" data-change>—</span></span>
                @if($isToday && $src === 'manual')
                  <button type="submit" form="release-{{ $r->id }}" class="text-[11px] font-semibold text-emerald-700 hover:underline ml-auto">↺ Use live</button>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach

    <div class="card p-4 flex flex-col sm:flex-row sm:items-end gap-3">
      <div class="flex-1">
        <label class="lbl" for="note">Note (optional)</label>
        <input id="note" name="note" class="inp" maxlength="255" placeholder="e.g. BAJUS announcement">
      </div>
      <div class="flex gap-2">
        <button type="button" id="copyLatest" class="btn btn-light">Fill with latest</button>
        <button class="btn btn-primary">Save manually for {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</button>
      </div>
    </div>
  </form>

  {{-- hidden forms for "Use live" (outside the main form) --}}
  @foreach($rates as $r)
    <form id="release-{{ $r->id }}" method="POST" action="{{ route('admin.rates.fetch') }}" class="hidden">
      @csrf <input type="hidden" name="release_purity_id" value="{{ $r->id }}">
    </form>
  @endforeach

  {{-- ===== Fetch log ===== --}}
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 font-bold text-sm">Fetch log</div>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Time</th><th>Status</th><th>Trigger</th><th>Details</th><th class="hidden sm:table-cell">Took</th></tr></thead>
        <tbody>
          @forelse($logs as $l)
            <tr>
              <td class="whitespace-nowrap text-xs">{{ $l->created_at->format('M d, H:i:s') }}</td>
              <td><span class="badge {{ $statusColor[$l->status] ?? 'bg-gray-100' }}">{{ $l->status }}</span></td>
              <td class="text-xs capitalize whitespace-nowrap">{{ $l->trigger }}{{ $l->user ? ' · '.$l->user->name : '' }}</td>
              <td class="text-xs text-gray-600 min-w-[220px]">
                {{ $l->message }}
                @foreach($l->changes ?? [] as $c)
                  <span class="block mono text-[11px]">{{ $c['label'] }}: {{ $c['old'] !== null ? bdt($c['old']) : '—' }} → <b>{{ bdt($c['new']) }}</b></span>
                @endforeach
              </td>
              <td class="text-xs mono hidden sm:table-cell">{{ $l->duration_ms }}ms</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-gray-400 py-6">No fetches yet. Click "Fetch live now".</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  const VHORI = {{ $vhori }};
  const fmt = n => '৳' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 2 });
  function refresh(row) {
    const inp = row.querySelector('[data-price]');
    const v = parseFloat(inp.value), latest = parseFloat(inp.dataset.latest) || 0;
    row.querySelector('[data-vhori]').textContent = isNaN(v) ? '—' : fmt(v * VHORI);
    const ch = row.querySelector('[data-change]');
    if (isNaN(v)) { ch.textContent = '—'; ch.className = 'mono font-semibold'; return; }
    const d = v - latest;
    ch.textContent = (d > 0 ? '+' : d < 0 ? '−' : '') + fmt(Math.abs(d));
    ch.className = 'mono font-semibold ' + (d > 0 ? 'text-green-600' : d < 0 ? 'text-red-600' : 'text-gray-500');
  }
  document.querySelectorAll('[data-rate-row]').forEach(row => {
    row.querySelector('[data-price]').addEventListener('input', () => refresh(row));
    refresh(row);
  });
  document.getElementById('copyLatest').addEventListener('click', () => {
    document.querySelectorAll('[data-rate-row]').forEach(row => {
      const inp = row.querySelector('[data-price]');
      if (!inp.value && inp.dataset.latest > 0) inp.value = inp.dataset.latest;
      refresh(row);
    });
  });
</script>
@endpush
