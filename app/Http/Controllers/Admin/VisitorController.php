<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $query = Visitor::query()->latest('last_visit_at');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(fn ($w) => $w->where('ip_address', 'like', "%{$q}%")
                ->orWhere('user_agent', 'like', "%{$q}%")
                ->orWhere('visitor_key', 'like', "%{$q}%")
                ->orWhere('first_referrer', 'like', "%{$q}%"));
        }
        if ($request->filled('device')) {
            $query->where('device_type', $request->string('device'));
        }
        if ($request->filled('bots')) {
            $query->where('is_bot', $request->input('bots') === 'only');
        }
        if ($request->filled('from')) {
            $query->whereDate('last_visit_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('last_visit_at', '<=', $request->date('to'));
        }

        return view('admin.visitors.index', ['items' => $query->paginate(30)->withQueryString()]);
    }

    public function show(Visitor $visitor)
    {
        return view('admin.visitors.show', [
            'visitor' => $visitor,
            'visits' => $visitor->visits()->latest('visited_at')->paginate(30),
            'estimates' => $visitor->estimates()->latest()->limit(20)->get(),
        ]);
    }

    public function destroy(Visitor $visitor)
    {
        $visitor->delete();

        return redirect()->route('admin.visitors.index')->with('success', 'Visitor and their visits deleted.');
    }
}
