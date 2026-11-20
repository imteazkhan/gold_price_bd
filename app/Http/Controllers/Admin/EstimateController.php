<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavedEstimate;

class EstimateController extends Controller
{
    public function index()
    {
        return view('admin.estimates.index', [
            'items' => SavedEstimate::with('visitor')->latest()->paginate(30),
        ]);
    }

    public function destroy(SavedEstimate $estimate)
    {
        $estimate->delete();

        return back()->with('success', 'Estimate deleted.');
    }
}
