<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountryPrice;
use App\Models\Purity;
use Illuminate\Http\Request;

class CountryPriceController extends Controller
{
    public function index()
    {
        return view('admin.countries.index', [
            'items' => CountryPrice::orderBy('karat')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.countries.form', ['item' => new CountryPrice(['is_active' => true, 'karat' => '24K']), 'karats' => $this->karats()]);
    }

    public function store(Request $request)
    {
        CountryPrice::create($this->validated($request));

        return redirect()->route('admin.countries.index')->with('success', 'Country price added.');
    }

    public function edit(CountryPrice $country)
    {
        return view('admin.countries.form', ['item' => $country, 'karats' => $this->karats()]);
    }

    public function update(Request $request, CountryPrice $country)
    {
        $country->update($this->validated($request));

        return redirect()->route('admin.countries.index')->with('success', 'Country price updated.');
    }

    public function destroy(CountryPrice $country)
    {
        $country->delete();

        return back()->with('success', 'Country price deleted.');
    }

    private function karats()
    {
        return Purity::where('metal', 'gold')->ordered()->pluck('karat');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'country' => 'required|string|max:255',
            'flag' => 'nullable|string|max:16',
            'currency' => 'required|string|max:10',
            'karat' => 'required|string|max:30',
            'local_price' => 'required|numeric|min:0',
            'fx_to_bdt' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['currency'] = strtoupper($data['currency']);

        return $data;
    }
}
