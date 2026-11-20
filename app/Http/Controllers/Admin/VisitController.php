<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $query = Visit::with('visitor')->latest('visited_at');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(fn ($w) => $w->where('path', 'like', "%{$q}%")
                ->orWhere('ip_address', 'like', "%{$q}%")
                ->orWhere('referrer', 'like', "%{$q}%"));
        }
        if ($request->filled('device')) {
            $query->where('device_type', $request->string('device'));
        }
        if ($request->filled('from')) {
            $query->whereDate('visited_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('visited_at', '<=', $request->date('to'));
        }

        return view('admin.visits.index', ['items' => $query->paginate(50)->withQueryString()]);
    }

    /** Delete visit records older than N days. */
    public function purge(Request $request)
    {
        $days = $request->validate(['days' => 'required|integer|min:1|max:3650'])['days'];
        $deleted = Visit::where('visited_at', '<', now()->subDays($days))->delete();

        return back()->with('success', "{$deleted} visit record(s) older than {$days} days deleted.");
    }
}
