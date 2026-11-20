<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Purity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PriceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = PriceHistory::with(['purity', 'editor'])->orderByDesc('recorded_on')->orderBy('purity_id');

        if ($request->filled('purity_id')) {
            $query->where('purity_id', $request->integer('purity_id'));
        }
        if ($request->filled('metal')) {
            $query->whereHas('purity', fn ($q) => $q->where('metal', $request->string('metal')));
        }
        if ($request->filled('from')) {
            $query->whereDate('recorded_on', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('recorded_on', '<=', $request->date('to'));
        }

        return view('admin.history.index', [
            'items' => $query->paginate(30)->withQueryString(),
            'purities' => Purity::ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.history.form', ['item' => new PriceHistory(['recorded_on' => now()]), 'purities' => Purity::ordered()->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        PriceHistory::create($data + ['updated_by' => $request->user()->id, 'source' => 'manual']);

        return redirect()->route('admin.history.index')->with('success', 'Price record added.');
    }

    public function edit(PriceHistory $history)
    {
        return view('admin.history.form', ['item' => $history, 'purities' => Purity::ordered()->get()]);
    }

    public function update(Request $request, PriceHistory $history)
    {
        $history->update($this->validated($request, $history) + ['updated_by' => $request->user()->id, 'source' => 'manual']);

        return redirect()->route('admin.history.index')->with('success', 'Price record updated.');
    }

    public function destroy(PriceHistory $history)
    {
        $history->delete();

        return back()->with('success', 'Price record deleted.');
    }

    private function validated(Request $request, ?PriceHistory $item = null): array
    {
        return $request->validate([
            'purity_id' => 'required|exists:purities,id',
            'recorded_on' => [
                'required', 'date',
                Rule::unique('price_histories')->where('purity_id', $request->input('purity_id'))->ignore($item?->id),
            ],
            'price_per_gram' => 'required|numeric|min:0|max:99999999',
            'note' => 'nullable|string|max:255',
        ], ['recorded_on.unique' => 'A price already exists for this purity on that date.']);
    }
}
