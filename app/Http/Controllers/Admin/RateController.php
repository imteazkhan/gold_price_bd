<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceHistory;
use App\Models\Purity;
use App\Models\RateFetchLog;
use App\Models\Setting;
use App\Services\RateService;
use App\Services\RateSyncService;
use Illuminate\Http\Request;

/**
 * "Update rates" screen: manual entry (one input per purity) plus the
 * live auto-update controls and fetch log.
 */
class RateController extends Controller
{
    public function index(Request $request, RateService $rates, RateSyncService $sync)
    {
        $date = $request->date('date') ?? now();
        $todayRows = PriceHistory::whereDate('recorded_on', $date)->get()->keyBy('purity_id');

        return view('admin.rates.index', [
            'rates' => $rates->currentRates(),
            'date' => $date->toDateString(),
            'existing' => $todayRows->map->price_per_gram,
            'sources' => $todayRows->map->source,
            'vhori' => $rates->vhoriGrams(),
            'settings' => Setting::all_settings(),
            'sync' => $sync,
            'logs' => RateFetchLog::with('user')->latest('created_at')->limit(15)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recorded_on' => 'required|date|before_or_equal:today',
            'prices' => 'required|array',
            'prices.*' => 'nullable|numeric|min:0|max:99999999',
            'note' => 'nullable|string|max:255',
            'only_changed' => 'nullable|boolean',
        ]);

        $validIds = Purity::pluck('id')->all();
        $existing = PriceHistory::whereDate('recorded_on', $data['recorded_on'])->get()->keyBy('purity_id');
        $saved = 0;

        foreach ($data['prices'] as $purityId => $price) {
            $purityId = (int) $purityId;
            if ($price === null || $price === '' || ! in_array($purityId, $validIds, true)) {
                continue;
            }
            // Don't flip an untouched auto price into a "manual" one
            $row = $existing[$purityId] ?? null;
            if ($row && (float) $row->price_per_gram === (float) $price && $row->source !== 'manual') {
                continue;
            }
            PriceHistory::updateOrCreate(
                ['purity_id' => $purityId, 'recorded_on' => $data['recorded_on']],
                [
                    'price_per_gram' => $price,
                    'note' => $data['note'] ?? null,
                    'source' => 'manual',
                    'updated_by' => $request->user()->id,
                ],
            );
            $saved++;
        }

        return redirect()->route('admin.rates.index', ['date' => $data['recorded_on']])
            ->with('success', "{$saved} rate(s) saved manually for {$data['recorded_on']}.");
    }

    /** "Fetch now" button: always runs, even if auto update is off. */
    public function fetch(Request $request, RateSyncService $sync)
    {
        // Optional: give one purity back to auto mode by removing today's manual price first
        if ($request->filled('release_purity_id')) {
            PriceHistory::where('purity_id', $request->integer('release_purity_id'))
                ->whereDate('recorded_on', now()->toDateString())
                ->where('source', 'manual')
                ->delete();
        }

        $log = $sync->sync('admin', force: true, userId: $request->user()->id);

        if (! $log) {
            return back()->with('error', 'Another fetch is already running. Try again in a moment.');
        }

        return back()->with($log->status === 'failed' ? 'error' : 'success', 'Live fetch: '.$log->message);
    }

    public function autoSettings(Request $request)
    {
        $data = $request->validate([
            'auto_update_interval' => 'required|integer|min:5|max:1440',
            'auto_update_max_jump' => 'required|numeric|min:1|max:100',
            'live_refresh_seconds' => 'required|integer|min:30|max:3600',
        ]);

        foreach (['auto_update_enabled', 'auto_update_override_manual', 'auto_update_usd'] as $flag) {
            $data[$flag] = $request->boolean($flag) ? '1' : '0';
        }
        Setting::put($data);

        return back()->with('success', 'Auto update settings saved.');
    }
}
