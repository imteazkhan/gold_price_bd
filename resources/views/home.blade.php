@extends('layouts.site')

@push('head')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    .info-card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:18px; transition:box-shadow .2s ease, border-color .2s ease; }
    .info-card:hover { box-shadow:0 10px 20px -10px rgba(0,0,0,.12); border-color:#d1d5db; }
    .info-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; flex-shrink:0; }

    .pill { display:inline-flex; align-items:center; gap:2px; font-size:10px; font-weight:700; padding:2px 7px; border-radius:999px; white-space:nowrap; }
    .pill-up { background:#dcfce7; color:#15803d; }
    .pill-down { background:#fee2e2; color:#b91c1c; }
    .pill-flat { background:#f1f5f9; color:#64748b; }

    .history-tab { font-size:12px; font-weight:600; padding:6px 12px; border-radius:8px; color:#64748b; transition:all .15s ease; white-space:nowrap; cursor:pointer; flex-shrink:0; }
    .history-tab:hover { background:#f1f5f9; }
    .history-tab.active { background:#f59e0b; color:#fff; }
    .unit-switch { font-size:12px; font-weight:600; padding:6px 14px; border-radius:8px; color:#64748b; transition:all .15s ease; white-space:nowrap; cursor:pointer; border:1px solid transparent; }
    .unit-switch:hover { background:#f1f5f9; }
    .unit-switch.active { background:#fff; color:#1e293b; border-color:#e2e8f0; box-shadow:0 1px 2px rgba(0,0,0,.06); }

    .history-table th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; padding:10px 8px; text-align:left; border-bottom:1px solid #e5e7eb; white-space:nowrap; background:#fff; position:sticky; top:0; z-index:1; }
    .history-table td { padding:11px 8px; font-size:12px; border-bottom:1px solid #f1f5f9; white-space:nowrap; }
    .history-table tbody tr:hover { background:#fafafa; }
    .trend-up { color:#15803d; font-weight:600; }
    .trend-down { color:#b91c1c; font-weight:600; }
    .trend-flat { color:#64748b; font-weight:600; }
    .spark-bar { display:inline-block; height:6px; width:70px; border-radius:3px; background:#e2e8f0; position:relative; overflow:hidden; vertical-align:middle; }
    .spark-bar > span { position:absolute; left:0; top:0; bottom:0; border-radius:3px; }

    .chart-container { position:relative; height:260px; width:100%; }
    .karat-btn.active { border-color:#f59e0b; background:#fffbeb; color:#78350f; font-weight:700; box-shadow:0 0 0 1px #f59e0b; }

    @media (max-width: 640px) {
      .history-table th, .history-table td { padding:8px 6px; font-size:10.5px; }
      .spark-bar { width:44px; }
      .chart-container { height:210px; }
      .history-tab { padding:5px 10px; font-size:11.5px; }
      .unit-switch { padding:5px 10px; font-size:11.5px; }
    }
  </style>
@endpush

@section('content')
  {{-- ===== Top bar ===== --}}
  <div class="bg-white border-b border-gray-200 text-[11px] sm:text-xs py-2 px-4 sm:px-6">
    <div class="max-w-6xl mx-auto flex items-center justify-between gap-3">
      <span class="text-gray-600 truncate">
<span class="inline-flex items-center gap-1.5">
          @if($settings['auto_update_enabled'])<span class="relative flex h-2 w-2" title="Live auto update"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>@endif
          Updated: <span id="liveUpdated">{{ $lastUpdated ? ($lastUpdated->isToday() ? 'today '.$lastUpdated->format('g:i A') : $lastUpdated->format('M d, Y g:i A')) : '—' }}</span>
        </span>
      </span>
      <div class="flex items-center gap-3 sm:gap-4 text-gray-500 shrink-0">
        @if($settings['usd_bdt'])<span class="mono">USD/BDT <span id="liveUsd">{{ number_format((float) $settings['usd_bdt'], 2) }}</span></span>@endif
        @if($settings['gold_int_usd'])<span class="text-gray-300 hidden md:inline">|</span><span class="mono hidden md:inline">Gold(Int) ${{ number_format((float) $settings['gold_int_usd']) }}/oz</span>@endif
        @if($settings['silver_int_usd'])<span class="text-gray-300 hidden md:inline">|</span><span class="mono hidden md:inline">Silver(Int) ${{ number_format((float) $settings['silver_int_usd'], 2) }}/oz</span>@endif
      </div>
    </div>
  </div>

  {{-- ===== Header / Nav ===== --}}
  <header class="bg-white/95 backdrop-blur border-b border-gray-200 sticky top-0 z-30">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14 sm:h-16">
      <a href="{{ route('home') }}" class="text-xl font-bold text-gray-900">
        @php $name = $settings['site_name']; @endphp
        @if(str_ends_with($name, 'Gold'))
          {{ substr($name, 0, -4) }}<span class="text-amber-600">Gold</span>
        @else
          {{ $name }}
        @endif
      </a>
      <nav class="hidden md:flex items-center space-x-6 text-sm font-medium text-gray-600" id="desktopNav">
        <a href="#rates" class="hover:text-amber-600">Rates</a>
        <a href="#info" class="hover:text-amber-600">Info</a>
        <a href="#history" class="hover:text-amber-600">History</a>
        <a href="#calculator" class="hover:text-amber-600">Calculator</a>
        @if($countries->isNotEmpty())<a href="#compare" class="hover:text-amber-600">Compare</a>@endif
        @if($faqs->isNotEmpty())<a href="#faq" class="hover:text-amber-600">FAQ</a>@endif
      </nav>
      <button id="copyRatesBtn" type="button" class="text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white px-3.5 py-2 rounded-full transition flex items-center gap-1.5 shadow-sm">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        <span>Copy rates</span>
      </button>
    </div>
    {{-- Mobile nav: horizontally scrollable --}}
    <nav class="md:hidden border-t border-gray-100 overflow-x-auto no-scrollbar">
      <div class="flex gap-1 px-3 py-1.5 text-[13px] font-medium text-gray-600 whitespace-nowrap">
        <a href="#rates" class="px-3 py-1 rounded-full hover:bg-amber-50 hover:text-amber-700">Rates</a>
        <a href="#info" class="px-3 py-1 rounded-full hover:bg-amber-50 hover:text-amber-700">Info</a>
        <a href="#history" class="px-3 py-1 rounded-full hover:bg-amber-50 hover:text-amber-700">History</a>
        <a href="#calculator" class="px-3 py-1 rounded-full hover:bg-amber-50 hover:text-amber-700">Calculator</a>
        @if($countries->isNotEmpty())<a href="#compare" class="px-3 py-1 rounded-full hover:bg-amber-50 hover:text-amber-700">Compare</a>@endif
        @if($faqs->isNotEmpty())<a href="#faq" class="px-3 py-1 rounded-full hover:bg-amber-50 hover:text-amber-700">FAQ</a>@endif
      </div>
    </nav>
  </header>

  <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-10 sm:space-y-12">

    {{-- ===== Hero + featured cards ===== --}}
    <section>
      <div class="pb-5 sm:pb-6 border-b border-gray-200">
        <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $settings['hero_title'] }}</h1>
        @if($settings['hero_subtitle'])
          <p class="mt-2 text-gray-500 text-sm max-w-2xl">{{ $settings['hero_subtitle'] }}</p>
        @endif
      </div>

      @if($featured->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-{{ min(3, $featured->count()) }} gap-3 sm:gap-4 mt-5 sm:mt-6">
          @foreach($featured as $p)
            @php $odd = $featured->count() % 2 === 1 && $loop->last; @endphp
            <div class="{{ $odd ? 'col-span-2 sm:col-span-1' : '' }} bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-sm flex items-start justify-between gap-2">
              <div class="min-w-0">
                <div class="text-[10px] sm:text-xs font-bold uppercase tracking-wide {{ $p->metal === 'gold' ? 'text-amber-700' : 'text-gray-500' }} truncate">
                  {{ $p->metal === 'gold' ? $p->karat : 'Silver '.$p->karat }} {{ $p->tagline ? '· '.$p->tagline : '' }}
                </div>
                <div class="text-lg sm:text-2xl font-extrabold mono mt-1"><span data-live="{{ $p->id }}" data-f="price">{{ bdt($p->price) }}</span> <span class="text-[10px] sm:text-xs font-normal text-gray-400">/g</span></div>
                <div class="text-[10px] sm:text-xs text-gray-500 mt-1 mono truncate"><span data-live="{{ $p->id }}" data-f="vhori">{{ bdt($p->vhori) }}</span> / vhori</div>
                @php $cls = $p->change > 0 ? 'pill-up' : ($p->change < 0 ? 'pill-down' : 'pill-flat'); @endphp
                <span class="pill {{ $cls }} mt-1.5" data-live="{{ $p->id }}" data-f="pill">
                  {{ signed_bdt($p->change) }} @if($p->change != 0)({{ number_format(abs($p->change_percent), 2) }}%)@endif
                </span>
              </div>
              <span class="{{ $p->metal === 'gold' ? ($loop->first ? 'bg-amber-100 text-amber-800' : 'bg-gray-800 text-white') : 'bg-gray-200 text-gray-700' }} text-[10px] sm:text-xs font-bold px-1.5 sm:px-2 py-1 rounded-lg shrink-0">
                {{ $p->metal === 'gold' ? $p->karat : 'Ag' }}
              </span>
            </div>
          @endforeach
        </div>
      @endif
    </section>

    {{-- ===== Rate tables ===== --}}
    <section id="rates">
      <div class="flex items-center justify-between mb-4 sm:mb-5 gap-2">
        <h2 class="text-lg sm:text-xl font-bold text-gray-900">Precious metal rates</h2>
        <span class="text-[11px] sm:text-xs text-gray-400 font-mono">1 vhori = {{ rtrim(rtrim(number_format($vhori, 3), '0'), '.') }} g</span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @foreach(['gold' => $gold, 'silver' => $silver] as $metal => $list)
          @php $list = $list->where('show_in_table', true); @endphp
          @continue($list->isEmpty())
          <div class="overflow-hidden rounded-xl border border-[#e4cda7] bg-white shadow-sm">
            <h3 class="bg-[#faf7f0] px-4 py-3 text-sm font-bold text-gray-900">{{ ucfirst($metal) }} rates</h3>
            <table class="w-full text-left text-sm">
              <thead class="bg-[#50111d] text-amber-200">
                <tr>
                  <th class="px-3 sm:px-4 py-2.5 text-xs font-bold">Purity</th>
                  <th class="px-3 sm:px-4 py-2.5 text-right text-xs font-bold">Vhori</th>
                  <th class="px-3 sm:px-4 py-2.5 text-right text-xs font-bold">Gram</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#eee4d5]">
                @foreach($list as $p)
                  <tr class="{{ $loop->odd ? 'bg-[#faf7f0]' : '' }}">
                    <th scope="row" class="px-3 sm:px-4 py-3 sm:py-4 font-medium text-gray-800 text-[13px] sm:text-sm">
                      {{ $p->label }}
                      <span data-live="{{ $p->id }}" data-f="change" class="block sm:inline sm:ml-1 text-[10px] font-semibold {{ $p->change > 0 ? 'text-green-600' : ($p->change < 0 ? 'text-red-600' : 'hidden') }}">{{ $p->change != 0 ? signed_bdt($p->change) : '' }}</span>
                    </th>
                    <td class="px-3 sm:px-4 py-3 sm:py-4 text-right font-bold text-gray-900 mono text-[12px] sm:text-sm" data-live="{{ $p->id }}" data-f="vhori2">{{ bdt($p->vhori, 2) }}</td>
                    <td class="px-3 sm:px-4 py-3 sm:py-4 text-right font-bold text-gray-900 mono text-[12px] sm:text-sm" data-live="{{ $p->id }}" data-f="price2">{{ bdt($p->price, 2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endforeach
      </div>
    </section>

    {{-- ===== Karat guide ===== --}}
    @php
      $guideMain = $rates->filter(fn ($p) => $p->metal === 'gold' && $p->purity_percent && $p->description);
      $guideExtra = $rates->filter(fn ($p) => ! ($p->metal === 'gold' && $p->purity_percent) && $p->description);
    @endphp
    @if($guideMain->isNotEmpty() || $guideExtra->isNotEmpty())
      <section id="info">
        <div class="mb-4 sm:mb-5">
          <h2 class="text-lg sm:text-xl font-bold text-gray-900">Gold karat guide</h2>
          <p class="text-xs text-gray-500 mt-1">What each karat means — purity, use case, and who it's for.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          @foreach($guideMain as $p)
            <div class="info-card">
              <div class="flex items-start gap-3">
                <div class="info-icon bg-amber-100 text-amber-800">{{ $p->karat }}</div>
                <div>
                  <h3 class="font-bold text-gray-900 text-sm">{{ $p->label }} ({{ rtrim(rtrim($p->purity_percent, '0'), '.') }}%)</h3>
                  @if($p->tagline)<p class="text-[11px] text-amber-700 font-semibold mt-0.5">{{ $p->tagline }}</p>@endif
                </div>
              </div>
              <p class="text-xs text-gray-600 mt-3 leading-relaxed">{{ $p->description }}</p>
              <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px]">
                <span class="text-gray-500">Today /g</span>
                <span class="mono font-bold text-gray-900" data-live="{{ $p->id }}" data-f="price">{{ bdt($p->price) }}</span>
              </div>
            </div>
          @endforeach
        </div>

        @if($guideExtra->isNotEmpty())
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mt-3 sm:mt-4">
            @foreach($guideExtra as $p)
              @php $isGold = $p->metal === 'gold'; @endphp
              <div class="info-card {{ $isGold ? 'bg-amber-50/40 border-amber-100' : 'bg-slate-50/60 border-slate-100' }}">
                <div class="flex items-start gap-3">
                  <div class="info-icon {{ $isGold ? 'bg-amber-200/70 text-amber-900' : 'bg-slate-200 text-slate-700' }}">{{ $isGold ? \Illuminate\Support\Str::limit($p->karat, 4, '') : 'Ag' }}</div>
                  <div>
                    <h3 class="font-bold text-gray-900 text-sm">{{ $p->label }}</h3>
                    @if($p->tagline)<p class="text-[11px] {{ $isGold ? 'text-amber-700' : 'text-slate-600' }} font-semibold mt-0.5">{{ $p->tagline }}</p>@endif
                  </div>
                </div>
                <p class="text-xs text-gray-600 mt-3 leading-relaxed">{{ $p->description }}</p>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px]">
                  <span class="text-gray-500">Today /g</span>
                  <span class="mono font-bold text-gray-900" data-live="{{ $p->id }}" data-f="price">{{ bdt($p->price) }}</span>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </section>
    @endif

    {{-- ===== Price history ===== --}}
    <section id="history" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="p-4 sm:p-6 border-b border-gray-200">
        <div class="flex flex-col gap-4">
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900">Price history</h2>
            <p class="text-xs text-gray-500 mt-1">Compare gold and silver prices over your selected period.</p>
          </div>
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 min-w-0">
              <div class="flex items-center bg-gray-100 rounded-lg p-0.5 w-fit" role="group" aria-label="Select metal">
                @foreach(array_keys(array_filter($purityMap, fn ($k) => count($k) > 0)) as $metal)
                  <button type="button" class="history-tab {{ $loop->first ? 'active' : '' }}" data-metal="{{ $metal }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ ucfirst($metal) }}</button>
                @endforeach
              </div>
              <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar -mx-1 px-1" id="purityTabs" role="group" aria-label="Select purity"></div>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-500">Unit</span>
              <div class="flex items-center bg-gray-100 rounded-lg p-0.5" role="group" aria-label="Select price unit">
                <button type="button" class="unit-switch active" data-unit="gram">Gram</button>
                <button type="button" class="unit-switch" data-unit="vhori">Vhori</button>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar -mx-1 px-1 pb-1" role="group" aria-label="Select history period">
            @foreach(['1' => '1D', '7' => '1W', '30' => '1M', '90' => '3M', '180' => '6M', '365' => '1Y', 'all' => 'All'] as $val => $lbl)
              <button type="button" class="history-tab {{ $val === '30' ? 'active' : '' }}" data-range="{{ $val }}" aria-pressed="{{ $val === '30' ? 'true' : 'false' }}">{{ $lbl }}</button>
            @endforeach
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 border-b border-gray-200 [&>div]:border-gray-100">
        <div class="p-3 sm:p-4 border-r border-b sm:border-b-0">
          <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Latest price</p>
          <p class="mono text-base sm:text-lg font-extrabold text-gray-900 mt-0.5" id="histLatest">—</p>
        </div>
        <div class="p-3 sm:p-4 border-b sm:border-b-0 sm:border-r">
          <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Period start</p>
          <p class="mono text-base sm:text-lg font-extrabold text-gray-900 mt-0.5" id="histStart">—</p>
        </div>
        <div class="p-3 sm:p-4 border-r">
          <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Change in period</p>
          <p class="mono text-sm sm:text-lg font-extrabold text-gray-900 mt-0.5" id="histChange">—</p>
        </div>
        <div class="p-3 sm:p-4">
          <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">High / Low</p>
          <p class="mono text-sm sm:text-lg font-extrabold text-gray-900 mt-0.5" id="histHighLow">—</p>
        </div>
      </div>

      <div class="p-3 sm:p-6 border-b border-gray-100 bg-gray-50/30">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-semibold text-gray-600" id="chartRangeLabel">Price trend · 1 month</p>
          <p class="text-[10px] text-gray-400 font-mono" id="chartUnitLabel">BDT / gram</p>
        </div>
        <div class="chart-container">
          <canvas id="priceChart" aria-label="Price history chart" role="img"></canvas>
          <div id="chartLoading" class="absolute inset-0 hidden items-center justify-center text-xs text-gray-400 bg-white/60">Loading…</div>
        </div>
      </div>

      <div class="overflow-auto max-h-[420px]">
        <table class="history-table w-full">
          <thead>
            <tr>
              <th>Date</th>
              <th id="tablePriceHeader">Price /g</th>
              <th>Change</th>
              <th>Trend</th>
              <th class="hidden sm:table-cell" id="tableSecondaryHeader">Vhori</th>
            </tr>
          </thead>
          <tbody id="historyBody">
            <tr><td colspan="5" class="text-center text-gray-400 py-6">Loading…</td></tr>
          </tbody>
        </table>
      </div>

      <div class="p-3 sm:p-4 border-t border-gray-100">
        <p class="text-[10px] text-gray-400 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
          <span id="historyDataNote">Prices per gram as announced by {{ $settings['rates_source'] ?: 'the jewellers association' }}.</span>
        </p>
      </div>
    </section>

    {{-- ===== Calculator ===== --}}
    @if($calculatorRates->isNotEmpty())
      @php $defaultCalc = $calculatorRates->firstWhere('karat', '22K') ?? $calculatorRates->first(); @endphp
      <section id="calculator" class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-5">
          <div class="lg:col-span-3 p-5 sm:p-8 border-b lg:border-b-0 lg:border-r border-gray-200">
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2">
              <span class="w-7 h-7 bg-amber-100 text-amber-700 rounded-lg flex items-center justify-center text-sm">৳</span>
              Jewelry value &amp; making cost
            </h2>
            <p class="text-gray-500 text-xs mt-1 mb-5">Transparent breakdown: metal, making charges, VAT.</p>

            <div class="mb-5">
              <label class="text-xs font-bold uppercase tracking-wide text-gray-500 block mb-2">Purity</label>
              <div class="grid grid-cols-{{ min(4, $calculatorRates->count()) }} gap-2">
                @foreach($calculatorRates as $p)
                  <button type="button" data-karat="{{ $p->karat }}" data-rate="{{ $p->price }}" data-live-rate="{{ $p->id }}"
                    class="karat-btn {{ $p->id === $defaultCalc->id ? 'active' : '' }} border border-gray-200 hover:border-gray-300 font-medium text-gray-700 py-2 rounded-xl text-xs flex flex-col items-center">
                    <span>{{ $p->karat }}</span><span class="text-[10px] opacity-80 font-normal" data-live="{{ $p->id }}" data-f="price">{{ bdt($p->price) }}</span>
                  </button>
                @endforeach
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-4">
              <div>
                <label for="weightG" class="text-xs font-bold text-gray-700 block mb-1">Weight (grams)</label>
                <input id="weightG" inputmode="decimal" class="w-full mono text-sm font-bold bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500" type="number" min="0" value="{{ $vhori }}" step="0.01">
              </div>
              <div>
                <label for="weightV" class="text-xs font-bold text-gray-700 block mb-1">Vhori (bhori)</label>
                <input id="weightV" inputmode="decimal" class="w-full mono text-sm font-bold bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500" type="number" min="0" value="1.00" step="0.01">
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
              <div>
                <label for="makingPct" class="text-xs font-bold text-gray-700 block mb-1">Making charge (%)</label>
                <input id="makingPct" inputmode="decimal" class="w-full mono text-sm font-bold bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500" type="number" min="0" max="100" value="{{ (float) $settings['default_making_percent'] }}" step="0.5">
              </div>
              <div>
                <label class="text-xs font-bold text-gray-700 block mb-1">VAT</label>
                <input class="w-full text-xs font-semibold bg-gray-100 border border-gray-200 text-gray-500 rounded-xl px-3 py-2.5" readonly value="{{ (float) $settings['vat_percent'] }}% (NBR)">
              </div>
            </div>
          </div>

          <div class="lg:col-span-2 bg-gray-50 p-5 sm:p-8 flex flex-col justify-between">
            <div>
              <div class="text-xs font-bold uppercase tracking-wide text-amber-700 border-b border-gray-200 pb-3 mb-4">Estimated breakdown</div>
              <div class="space-y-3 text-sm mono">
                <div class="flex justify-between gap-2"><span class="text-gray-500">Gold base</span><span id="basePrice">—</span></div>
                <div class="flex justify-between gap-2"><span class="text-gray-500">Making (<span id="makingLabel">6</span>%)</span><span id="makingPrice">—</span></div>
                <div class="flex justify-between gap-2"><span class="text-gray-500">VAT ({{ (float) $settings['vat_percent'] }}%)</span><span id="vatPrice">—</span></div>
              </div>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-200">
              <div class="text-xs font-bold uppercase text-amber-700">Total payable</div>
              <div class="text-2xl sm:text-3xl font-extrabold mono tracking-tight break-all" id="totalPrice">—</div>
              <button type="button" id="saveEstimateBtn" class="w-full mt-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white font-bold text-xs uppercase tracking-wide transition">Save estimate</button>
              <p id="saveEstimateMsg" class="text-[11px] mt-2 hidden"></p>
              <p class="text-[10px] text-gray-400 mt-2">*Making charges vary by design (6-12%).</p>
            </div>
          </div>
        </div>
      </section>
    @endif

    {{-- ===== Global comparison ===== --}}
    @if($countries->isNotEmpty())
      <section id="compare" class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
          <h2 class="text-lg sm:text-xl font-bold text-gray-900">Gold price comparison (<span id="compareKaratLabel">{{ $compareKarats->first() }}</span> per gram)</h2>
          <select id="compareSelect" class="text-xs font-semibold border border-gray-300 rounded-lg px-3 py-1.5 bg-gray-50 w-fit">
            @foreach($compareKarats as $k)
              <option value="{{ $k }}">{{ $k }}</option>
            @endforeach
          </select>
        </div>
        <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
          <table class="w-full text-left text-sm min-w-[520px]">
            <thead>
              <tr class="border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                <th class="pb-3 pr-4">Country</th>
                <th class="pb-3 px-2">Local price</th>
                <th class="pb-3 px-2">FX to BDT</th>
                <th class="pb-3 px-2">BDT / gram</th>
                <th class="pb-3 pl-2 text-right">vs BD</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs sm:text-sm">
              @foreach($countries as $c)
                <tr data-karat="{{ $c->karat }}" class="{{ $c->karat !== $compareKarats->first() ? 'hidden' : '' }}">
                  <td class="py-3 pr-4 whitespace-nowrap">{{ $c->flag }} {{ $c->country }}</td>
                  <td class="py-3 px-2 mono whitespace-nowrap">{{ number_format($c->local_price, 2) }} {{ $c->currency }}</td>
                  <td class="py-3 px-2 mono">{{ bdt($c->fx_to_bdt, (float) $c->fx_to_bdt < 10 ? 2 : 0) }}</td>
                  <td class="py-3 px-2 mono font-semibold">{{ bdt($c->bdt_per_gram) }}</td>
                  <td class="py-3 pl-2 text-right font-medium whitespace-nowrap {{ $c->diff === null ? 'text-gray-400' : ($c->diff <= 0 ? 'text-green-600' : 'text-red-600') }}">
                    @if($c->diff === null) — @else {{ $c->diff <= 0 ? '−' : '+' }}৳{{ number_format(abs($c->diff)) }}/g @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @if($settings['compare_note'])
          <div class="mt-4 pt-3 border-t border-gray-200 text-xs text-gray-400 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            {{ $settings['compare_note'] }}
          </div>
        @endif
      </section>
    @endif

    {{-- ===== FAQ ===== --}}
    @if($faqs->isNotEmpty())
      <section id="faq" class="max-w-3xl mx-auto">
        <h2 class="text-lg sm:text-xl font-bold text-gray-900 text-center mb-5">Frequently asked questions</h2>
        <div class="space-y-3">
          @foreach($faqs as $faq)
            <details class="bg-white rounded-xl border border-gray-200 p-4" @if($loop->first) open @endif>
              <summary class="flex justify-between items-center gap-3 cursor-pointer font-semibold text-gray-800 text-sm">
                <span>{{ $faq->question }}</span>
                <span class="chevron text-gray-400 text-xs">▼</span>
              </summary>
              <p class="mt-3 text-xs text-gray-600 leading-relaxed border-t border-gray-100 pt-3">{!! nl2br(e($faq->answer)) !!}</p>
            </details>
          @endforeach
        </div>
      </section>
    @endif
  </main>

  <footer class="border-t border-gray-200 bg-white mt-12 py-6 text-xs text-gray-500">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-center sm:text-left">
      <div>© {{ now()->year }} {{ $settings['site_name'] }} · {{ $settings['footer_text'] }}</div>
      <div class="flex flex-wrap justify-center gap-4">
        @if($settings['contact_email'])<a href="mailto:{{ $settings['contact_email'] }}" class="hover:text-amber-600">{{ $settings['contact_email'] }}</a>@endif
        @if($settings['contact_phone'])<a href="tel:{{ $settings['contact_phone'] }}" class="hover:text-amber-600">{{ $settings['contact_phone'] }}</a>@endif
      </div>
    </div>
  </footer>

  {{-- toast --}}
  <div id="toast" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 hidden bg-gray-900 text-white text-xs font-semibold px-4 py-2.5 rounded-full shadow-lg"></div>
@endsection

@push('scripts')
<script>
  window.GP = {
    vhori: {{ $vhori }},
    vatPercent: {{ (float) $settings['vat_percent'] }},
    purityMap: @json($purityMap),
    historyUrl: @json(route('history.data')),
    estimateUrl: @json(route('estimates.store')),
    liveUrl: @json(route('rates.live')),
    liveEvery: {{ max(30, (int) $settings['live_refresh_seconds']) }},
    copyText: @json($copyText),
  };
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const GP = window.GP;
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const fmt = (n, d = 0) => '৳' + Number(n).toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
  const $ = id => document.getElementById(id);

  function toast(msg) {
    const t = $('toast');
    t.textContent = msg;
    t.classList.remove('hidden');
    clearTimeout(toast._t);
    toast._t = setTimeout(() => t.classList.add('hidden'), 2200);
  }

  // ---------- Copy rates ----------
  const copyBtn = $('copyRatesBtn');
  copyBtn?.addEventListener('click', async () => {
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(GP.copyText);
      } else {
        const ta = document.createElement('textarea');
        ta.value = GP.copyText; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove();
      }
      const label = copyBtn.querySelector('span');
      label.textContent = 'Copied!';
      copyBtn.classList.replace('bg-amber-500', 'bg-emerald-600');
      setTimeout(() => { label.textContent = 'Copy rates'; copyBtn.classList.replace('bg-emerald-600', 'bg-amber-500'); }, 2000);
    } catch (e) { toast('Could not copy'); }
  });

  // ---------- Calculator ----------
  const weightG = $('weightG'), weightV = $('weightV'), makingPct = $('makingPct');
  let selected = document.querySelector('.karat-btn.active');

  function calc() {
    if (!selected) return null;
    const grams = parseFloat(weightG.value) || 0;
    const rate = parseFloat(selected.dataset.rate) || 0;
    const mp = parseFloat(makingPct.value) || 0;
    const base = grams * rate, making = base * mp / 100, vat = (base + making) * GP.vatPercent / 100;
    $('basePrice').textContent = fmt(base, 2);
    $('makingPrice').textContent = fmt(making, 2);
    $('makingLabel').textContent = mp;
    $('vatPrice').textContent = fmt(vat, 2);
    $('totalPrice').textContent = fmt(base + making + vat, 2);
    return { karat: selected.dataset.karat, grams, mp };
  }

  if (weightG) {
    document.querySelectorAll('.karat-btn').forEach(btn => btn.addEventListener('click', () => {
      document.querySelectorAll('.karat-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      selected = btn;
      calc();
    }));
    weightG.addEventListener('input', () => { weightV.value = ((parseFloat(weightG.value) || 0) / GP.vhori).toFixed(3); calc(); });
    weightV.addEventListener('input', () => { weightG.value = ((parseFloat(weightV.value) || 0) * GP.vhori).toFixed(2); calc(); });
    makingPct.addEventListener('input', calc);
    calc();

    const saveBtn = $('saveEstimateBtn'), saveMsg = $('saveEstimateMsg');
    saveBtn.addEventListener('click', async () => {
      const c = calc();
      if (!c || c.grams <= 0) { toast('Enter a weight first'); return; }
      saveBtn.disabled = true;
      try {
        const res = await fetch(GP.estimateUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
          body: JSON.stringify({ karat: c.karat, weight_grams: c.grams, making_percent: c.mp }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed');
        saveMsg.textContent = `Saved (#${json.id}) · Total ${fmt(json.total, 2)}`;
        saveMsg.className = 'text-[11px] mt-2 text-emerald-600 font-semibold';
      } catch (e) {
        saveMsg.textContent = e.message || 'Could not save estimate.';
        saveMsg.className = 'text-[11px] mt-2 text-red-600 font-semibold';
      } finally {
        saveBtn.disabled = false;
      }
    });
  }

  // ---------- Compare ----------
  $('compareSelect')?.addEventListener('change', e => {
    const k = e.target.value;
    $('compareKaratLabel').textContent = k;
    document.querySelectorAll('#compare tr[data-karat]').forEach(tr => tr.classList.toggle('hidden', tr.dataset.karat !== k));
  });

  // ---------- History ----------
  const state = { metal: Object.keys(GP.purityMap).find(m => GP.purityMap[m].length) || 'gold', karat: null, unit: 'gram', range: '30' };
  const rangeNames = { '1': '1 day', '7': '1 week', '30': '1 month', '90': '3 months', '180': '6 months', '365': '12 months', all: 'all time' };
  const cache = new Map();
  let chart = null, controller = null;

  function setActive(selector, el) {
    document.querySelectorAll(selector).forEach(b => {
      const on = b === el;
      b.classList.toggle('active', on);
      b.setAttribute('aria-pressed', String(on));
    });
  }

  function renderPurityTabs() {
    const list = GP.purityMap[state.metal] || [];
    if (!list.includes(state.karat)) state.karat = list.includes('22K') ? '22K' : list[0];
    $('purityTabs').innerHTML = list.map(k =>
      `<button type="button" class="history-tab ${k === state.karat ? 'active' : ''}" data-karat="${k}" aria-pressed="${k === state.karat}">${k}</button>`
    ).join('');
  }

  $('purityTabs').addEventListener('click', e => {
    const btn = e.target.closest('[data-karat]');
    if (!btn) return;
    state.karat = btn.dataset.karat;
    setActive('#purityTabs [data-karat]', btn);
    load();
  });
  document.querySelectorAll('[data-metal]').forEach(btn => btn.addEventListener('click', () => {
    state.metal = btn.dataset.metal;
    setActive('[data-metal]', btn);
    renderPurityTabs();
    load();
  }));
  document.querySelectorAll('[data-range]').forEach(btn => btn.addEventListener('click', () => {
    state.range = btn.dataset.range;
    setActive('[data-range]', btn);
    load();
  }));
  document.querySelectorAll('.unit-switch').forEach(btn => btn.addEventListener('click', () => {
    state.unit = btn.dataset.unit;
    document.querySelectorAll('.unit-switch').forEach(b => b.classList.toggle('active', b === btn));
    load();
  }));

  async function load() {
    if (!state.karat) return;
    const key = `${state.metal}|${state.karat}|${state.range}`;
    let data = cache.get(key);
    if (!data) {
      controller?.abort();
      controller = new AbortController();
      $('chartLoading').classList.replace('hidden', 'flex');
      try {
        const url = `${GP.historyUrl}?${new URLSearchParams({ metal: state.metal, karat: state.karat, range: state.range })}`;
        const res = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
        data = await res.json();
        cache.set(key, data);
      } catch (e) {
        if (e.name === 'AbortError') return;
        data = { rows: [], total: 0 };
      } finally {
        $('chartLoading').classList.replace('flex', 'hidden');
      }
    }
    render(data);
  }

  function render({ rows, total }) {
    const m = state.unit === 'gram' ? 1 : GP.vhori;
    const silver = state.metal === 'silver';
    const dec = silver && state.unit === 'gram' ? 0 : 0;

    $('tablePriceHeader').textContent = state.unit === 'gram' ? 'Price /g' : 'Price /vhori';
    $('tableSecondaryHeader').textContent = state.unit === 'gram' ? 'Vhori' : 'Gram';
    $('chartUnitLabel').textContent = state.unit === 'gram' ? 'BDT / gram' : 'BDT / vhori';
    $('chartRangeLabel').textContent = `Price trend · ${rangeNames[state.range]}`;

    if (!rows.length) {
      ['histLatest', 'histStart', 'histChange', 'histHighLow'].forEach(id => $(id).textContent = '—');
      $('historyBody').innerHTML = '<tr><td class="text-center text-gray-500 py-6" colspan="5">No history available for this selection.</td></tr>';
      $('historyDataNote').textContent = 'No records yet.';
      chart?.destroy(); chart = null;
      return;
    }

    const latest = rows[0], earliest = rows[rows.length - 1];
    const prices = rows.map(r => r.price);
    const max = Math.max(...prices), min = Math.min(...prices);
    const change = (latest.price - earliest.price) * m;
    const pct = earliest.price ? (latest.price - earliest.price) / earliest.price * 100 : 0;

    $('histLatest').textContent = fmt(latest.price * m, dec);
    $('histStart').textContent = fmt(earliest.price * m, dec);
    const ch = $('histChange');
    ch.textContent = `${change > 0 ? '+' : change < 0 ? '−' : ''}${fmt(Math.abs(change))} (${pct > 0 ? '+' : ''}${pct.toFixed(2)}%)`;
    ch.className = `mono text-sm sm:text-lg font-extrabold mt-0.5 ${change > 0 ? 'text-green-600' : change < 0 ? 'text-red-600' : 'text-gray-900'}`;
    $('histHighLow').textContent = `${fmt(max * m)} / ${fmt(min * m)}`;

    $('historyBody').innerHTML = rows.map(r => {
      const pctW = Math.max(8, Math.min(100, ((r.price - min) / (max - min || 1)) * 100));
      const c = r.change * m;
      const cls = c > 0 ? 'trend-up' : c < 0 ? 'trend-down' : 'trend-flat';
      const txt = c > 0 ? `▲ +${fmt(c)}` : c < 0 ? `▼ −${fmt(Math.abs(c))}` : '— ৳0';
      const bar = c > 0 ? '#22c55e' : c < 0 ? '#ef4444' : '#94a3b8';
      const main = state.unit === 'gram' ? r.price : r.price * GP.vhori;
      const second = state.unit === 'gram' ? r.price * GP.vhori : r.price;
      return `<tr>
        <td class="text-gray-700 font-medium">${r.date}</td>
        <td class="mono font-bold text-gray-900">${fmt(main)}</td>
        <td class="${cls}">${txt}</td>
        <td><span class="spark-bar"><span style="width:${pctW}%;background:${bar}"></span></span></td>
        <td class="mono text-gray-600 hidden sm:table-cell">${fmt(second)}</td>
      </tr>`;
    }).join('');

    $('historyDataNote').textContent = `Showing ${rows.length} of ${total} records for ${state.karat} ${state.metal}.`;
    drawChart(rows.slice().reverse(), m, silver);
  }

  function drawChart(rows, m, silver) {
    const color = silver ? '#64748b' : '#f59e0b';
    const dense = rows.length > 45;
    const mobile = window.matchMedia('(max-width: 640px)').matches;
    chart?.destroy();
    chart = new Chart($('priceChart').getContext('2d'), {
      type: 'line',
      data: {
        labels: rows.map(r => r.date.replace(/, \d{4}$/, '')),
        datasets: [{
          data: rows.map(r => Math.round(r.price * m)),
          borderColor: color,
          backgroundColor: silver ? 'rgba(100,116,139,0.08)' : 'rgba(245,158,11,0.08)',
          borderWidth: dense ? 2 : 2.5,
          pointRadius: dense ? 0 : (mobile ? 2.5 : 4),
          pointBackgroundColor: color,
          pointBorderColor: '#fff',
          pointBorderWidth: 1.5,
          pointHoverRadius: 5,
          tension: 0.25,
          fill: true,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 300 },
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { title: items => rows[items[0].dataIndex].date, label: c => fmt(c.parsed.y) } },
        },
        scales: {
          y: { beginAtZero: false, grid: { color: '#f1f5f9' }, ticks: { font: { size: mobile ? 9 : 10, family: 'JetBrains Mono' }, maxTicksLimit: mobile ? 5 : 7, callback: v => fmt(v) } },
          x: { grid: { display: false }, ticks: { font: { size: mobile ? 9 : 10, family: 'Inter' }, maxTicksLimit: mobile ? 5 : 10, maxRotation: 0, autoSkip: true } },
        },
        interaction: { intersect: false, mode: 'index' },
      },
    });
  }

  renderPurityTabs();
  load();

  // ---------- Live price refresh ----------
  let lastStamp = null;
  const signed = c => Math.round(c) === 0 ? '— ৳0' : (c > 0 ? '▲ +' : '▼ −') + fmt(Math.abs(c));

  function flash(el) {
    el.classList.add('transition-colors', 'duration-700', 'bg-amber-100', 'rounded');
    setTimeout(() => el.classList.remove('bg-amber-100'), 1600);
  }

  function applyLive(data) {
    $('liveUpdated') && ($('liveUpdated').textContent = data.updated_label);
    if ($('liveUsd') && data.usd_bdt) $('liveUsd').textContent = data.usd_bdt.toFixed(2);
    GP.copyText = data.copy_text;

    let changed = false;
    data.rates.forEach(r => {
      document.querySelectorAll(`[data-live="${r.id}"]`).forEach(el => {
        const f = el.dataset.f;
        let text;
        if (f === 'price') text = fmt(r.price);
        else if (f === 'price2') text = fmt(r.price, 2);
        else if (f === 'vhori') text = fmt(r.vhori);
        else if (f === 'vhori2') text = fmt(r.vhori, 2);
        else if (f === 'change') {
          text = r.change ? signed(r.change) : '';
          el.classList.toggle('hidden', !r.change);
          el.classList.toggle('text-green-600', r.change > 0);
          el.classList.toggle('text-red-600', r.change < 0);
        } else if (f === 'pill') {
          text = signed(r.change) + (r.change ? ` (${Math.abs(r.change_percent).toFixed(2)}%)` : '');
          el.classList.remove('pill-up', 'pill-down', 'pill-flat');
          el.classList.add(r.change > 0 ? 'pill-up' : r.change < 0 ? 'pill-down' : 'pill-flat');
        }
        if (text !== undefined && el.textContent.trim() !== text) {
          el.textContent = text;
          if (f !== 'change' && f !== 'pill') { flash(el); changed = true; }
        }
      });
      document.querySelectorAll(`[data-live-rate="${r.id}"]`).forEach(btn => btn.dataset.rate = r.price);
    });

    if (changed) {
      calc();
      cache.clear();   // history now has a new latest point
      load();
      toast('Prices updated');
    }
  }

  async function pollLive() {
    if (document.hidden) return;
    try {
      const res = await fetch(GP.liveUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
      if (!res.ok) return;
      const data = await res.json();
      if (data.updated_at !== lastStamp || lastStamp === null) {
        lastStamp = data.updated_at;
        applyLive(data);
      }
    } catch (e) { /* offline — try again next tick */ }
  }

  pollLive();                                   // first check right away (may trigger a fetch on the server)
  setInterval(pollLive, GP.liveEvery * 1000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) pollLive(); });
});
</script>
@endpush
