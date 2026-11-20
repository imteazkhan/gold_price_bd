@extends('admin.layout')
@section('title', 'Visitor #'.$visitor->id)

@section('actions')
  <a href="{{ route('admin.visitors.index') }}" class="btn btn-light btn-sm">← Back</a>
@endsection

@section('content')
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="card p-4 sm:p-5 lg:col-span-1">
      <h2 class="font-bold text-sm mb-3">Visitor info</h2>
      <dl class="text-sm space-y-2">
        @foreach([
          'Visitor ID' => $visitor->visitor_key,
          'IP address' => $visitor->ip_address,
          'Device' => ucfirst($visitor->device_type).($visitor->is_bot ? ' (bot)' : ''),
          'Browser' => $visitor->browser,
          'Platform' => $visitor->platform,
          'Language' => $visitor->language ?: '—',
          'Total visits' => $visitor->total_visits,
          'First visit' => $visitor->first_visit_at?->format('M d, Y H:i:s'),
          'Last visit' => $visitor->last_visit_at?->format('M d, Y H:i:s'),
          'First landing' => $visitor->first_landing,
          'First referrer' => $visitor->first_referrer ?: 'Direct',
        ] as $k => $val)
          <div class="grid grid-cols-3 gap-2">
            <dt class="text-gray-500 text-xs">{{ $k }}</dt>
            <dd class="col-span-2 break-all text-xs mono">{{ $val }}</dd>
          </div>
        @endforeach
        <div>
          <dt class="text-gray-500 text-xs mb-1">User agent</dt>
          <dd class="text-[11px] mono text-gray-600 break-all bg-gray-50 rounded p-2">{{ $visitor->user_agent }}</dd>
        </div>
      </dl>
      <form method="POST" action="{{ route('admin.visitors.destroy', $visitor) }}" class="mt-4" data-confirm="Delete this visitor and all their visit records?">
        @csrf @method('DELETE')
        <button class="btn btn-danger btn-sm w-full">Delete visitor &amp; visits</button>
      </form>
    </div>

    <div class="lg:col-span-2 space-y-4">
      <div class="card overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 font-bold text-sm">Visit history ({{ $visits->total() }})</div>
        <div class="overflow-x-auto">
          <table class="tbl">
            <thead><tr><th>Time</th><th>Page</th><th class="hidden sm:table-cell">IP</th><th class="hidden md:table-cell">Referrer</th></tr></thead>
            <tbody>
              @foreach($visits as $v)
                <tr>
                  <td class="whitespace-nowrap text-xs">{{ $v->visited_at->format('M d, Y H:i:s') }}</td>
                  <td class="mono text-xs break-all">{{ $v->path }}</td>
                  <td class="mono text-xs hidden sm:table-cell">{{ $v->ip_address }}</td>
                  <td class="text-xs text-gray-500 hidden md:table-cell max-w-[220px] truncate" title="{{ $v->referrer }}">{{ $v->referrer ?: '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      {{ $visits->links() }}

      @if($estimates->isNotEmpty())
        <div class="card overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100 font-bold text-sm">Saved estimates</div>
          <div class="overflow-x-auto">
            <table class="tbl">
              <thead><tr><th>Date</th><th>Karat</th><th>Weight</th><th>Making</th><th>Total</th></tr></thead>
              <tbody>
                @foreach($estimates as $e)
                  <tr>
                    <td class="text-xs whitespace-nowrap">{{ $e->created_at->format('M d, H:i') }}</td>
                    <td>{{ $e->karat }}</td>
                    <td class="mono">{{ rtrim(rtrim($e->weight_grams, '0'), '.') }} g</td>
                    <td class="mono">{{ (float) $e->making_percent }}%</td>
                    <td class="mono font-bold">{{ bdt($e->total_amount, 2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
    </div>
  </div>
@endsection
