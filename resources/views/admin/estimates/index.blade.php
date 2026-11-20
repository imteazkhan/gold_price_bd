@extends('admin.layout')
@section('title', 'Saved estimates')

@section('content')
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Date</th><th>Karat</th><th>Weight</th><th class="hidden sm:table-cell">Rate /g</th><th class="hidden md:table-cell">Making</th><th class="hidden md:table-cell">VAT</th><th>Total</th><th class="hidden lg:table-cell">Visitor</th><th></th></tr></thead>
        <tbody>
          @forelse($items as $e)
            <tr>
              <td class="text-xs whitespace-nowrap">{{ $e->created_at->format('M d, Y H:i') }}</td>
              <td class="font-semibold">{{ $e->karat }}</td>
              <td class="mono whitespace-nowrap">{{ rtrim(rtrim($e->weight_grams, '0'), '.') }} g</td>
              <td class="mono hidden sm:table-cell">{{ bdt($e->rate_per_gram) }}</td>
              <td class="mono hidden md:table-cell whitespace-nowrap">{{ (float) $e->making_percent }}% · {{ bdt($e->making_amount) }}</td>
              <td class="mono hidden md:table-cell">{{ bdt($e->vat_amount) }}</td>
              <td class="mono font-bold whitespace-nowrap">{{ bdt($e->total_amount, 2) }}</td>
              <td class="hidden lg:table-cell text-xs">
                @if($e->visitor)<a href="{{ route('admin.visitors.show', $e->visitor) }}" class="text-amber-700 hover:underline mono">{{ $e->ip_address }}</a>@else<span class="mono">{{ $e->ip_address }}</span>@endif
              </td>
              <td class="text-right">
                <form method="POST" action="{{ route('admin.estimates.destroy', $e) }}" data-confirm="Delete this estimate?">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-gray-400 py-8">No saved estimates yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  {{ $items->links() }}
@endsection
