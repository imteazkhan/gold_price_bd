<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurityController extends Controller
{
    public function index()
    {
        return view('admin.purities.index', ['items' => Purity::withCount('histories')->ordered()->get()]);
    }

    public function create()
    {
        return view('admin.purities.form', ['item' => new Purity(['is_active' => true, 'show_in_table' => true, 'metal' => 'gold'])]);
    }

    public function store(Request $request)
    {
        Purity::create($this->validated($request));

        return redirect()->route('admin.purities.index')->with('success', 'Purity created.');
    }

    public function edit(Purity $purity)
    {
        return view('admin.purities.form', ['item' => $purity]);
    }

    public function update(Request $request, Purity $purity)
    {
        $purity->update($this->validated($request, $purity));

        return redirect()->route('admin.purities.index')->with('success', 'Purity updated.');
    }

    public function destroy(Purity $purity)
    {
        $purity->delete();

        return back()->with('success', 'Purity and its price history deleted.');
    }

    private function validated(Request $request, ?Purity $item = null): array
    {
        $data = $request->validate([
            'metal' => 'required|in:gold,silver',
            'karat' => [
                'required', 'string', 'max:30',
                Rule::unique('purities')->where('metal', $request->input('metal'))->ignore($item?->id),
            ],
            'label' => 'required|string|max:255',
            'purity_percent' => 'nullable|numeric|min:0|max:100',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        foreach (['is_featured', 'show_in_table', 'show_in_calculator', 'is_active'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
