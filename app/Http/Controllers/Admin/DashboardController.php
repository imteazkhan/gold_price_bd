<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavedEstimate;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\RateService;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(RateService $rates)
    {
        $today = now()->startOfDay();
        $from = now()->subDays(13)->startOfDay();

        $stats = [
            'visitors_total' => Visitor::where('is_bot', false)->count(),
            'visitors_today' => Visitor::where('is_bot', false)->where('last_visit_at', '>=', $today)->count(),
            'new_today' => Visitor::where('is_bot', false)->where('first_visit_at', '>=', $today)->count(),
            'visits_total' => Visit::count(),
            'visits_today' => Visit::where('visited_at', '>=', $today)->count(),
            'bots_total' => Visitor::where('is_bot', true)->count(),
            'estimates_total' => SavedEstimate::count(),
        ];

        // Daily visits + unique visitors for the last 14 days
        $daily = Visit::where('visited_at', '>=', $from)
            ->selectRaw('DATE(visited_at) as d, COUNT(*) as visits, COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('d')->get()->keyBy('d');
        $chart = collect(CarbonPeriod::create($from, now()))->map(fn ($day) => [
            'label' => $day->format('M d'),
            'visits' => (int) ($daily[$day->toDateString()]->visits ?? 0),
            'visitors' => (int) ($daily[$day->toDateString()]->visitors ?? 0),
        ])->values();

        $devices = Visitor::select('device_type', DB::raw('COUNT(*) as total'))
            ->groupBy('device_type')->orderByDesc('total')->pluck('total', 'device_type');
        $browsers = Visitor::where('is_bot', false)->select('browser', DB::raw('COUNT(*) as total'))
            ->groupBy('browser')->orderByDesc('total')->limit(6)->pluck('total', 'browser');
        $topPages = Visit::select('path', DB::raw('COUNT(*) as total'))
            ->groupBy('path')->orderByDesc('total')->limit(6)->get();
        $topReferrers = Visitor::whereNotNull('first_referrer')
            ->select('first_referrer', DB::raw('COUNT(*) as total'))
            ->groupBy('first_referrer')->orderByDesc('total')->limit(6)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'chart' => $chart,
            'devices' => $devices,
            'browsers' => $browsers,
            'topPages' => $topPages,
            'topReferrers' => $topReferrers,
            'recentVisits' => Visit::with('visitor')->latest('visited_at')->limit(8)->get(),
            'rates' => $rates->currentRates(),
            'lastUpdated' => $rates->lastUpdated(),
        ]);
    }
}
