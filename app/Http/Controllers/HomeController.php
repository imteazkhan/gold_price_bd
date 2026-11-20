<?php

namespace App\Http\Controllers;

use App\Http\Middleware\TrackVisit;
use App\Models\CountryPrice;
use App\Models\Visitor;
use App\Models\Faq;
use App\Models\Purity;
use App\Models\SavedEstimate;
use App\Models\Setting;
use App\Services\RateService;
use App\Services\RateSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private RateService $rates) {}

    public function index()
    {
        $rates = $this->rates->currentRates();
        $settings = Setting::all_settings();

        // BD price per gram for each gold karat (used by the comparison table)
        $bdGold = $rates->where('metal', 'gold')->mapWithKeys(fn ($p) => [$p->karat => $p->price]);

        $countries = CountryPrice::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()
            ->each(function (CountryPrice $c) use ($bdGold) {
                $c->bd_price = (float) ($bdGold[$c->karat] ?? 0);
                $c->diff = $c->bd_price > 0 ? $c->bdt_per_gram - $c->bd_price : null;
            });

        return view('home', [
            'settings' => $settings,
            'rates' => $rates,
            'gold' => $rates->where('metal', 'gold')->values(),
            'silver' => $rates->where('metal', 'silver')->values(),
            'featured' => $rates->where('is_featured', true)->take(3)->values(),
            'calculatorRates' => $rates->where('metal', 'gold')->where('show_in_calculator', true)->where('price', '>', 0)->values(),
            'countries' => $countries,
            'compareKarats' => $countries->pluck('karat')->unique()->values(),
            'faqs' => Faq::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'lastUpdated' => $this->rates->lastUpdated(),
            'copyText' => $this->rates->copyText($rates),
            'vhori' => $this->rates->vhoriGrams(),
            'purityMap' => [
                'gold' => $rates->where('metal', 'gold')->pluck('karat')->values(),
                'silver' => $rates->where('metal', 'silver')->pluck('karat')->values(),
            ],
        ]);
    }

    /**
     * Polled by the public page. When auto update is due this also triggers a
     * fetch, so prices stay fresh even without a cron job.
     */
    public function live(RateSyncService $sync): JsonResponse
    {
        try {
            $sync->syncIfDue('visitor');
        } catch (\Throwable) {
            // never break the public endpoint because the source is down
        }

        return response()->json($this->rates->liveSnapshot())
            ->header('Cache-Control', 'no-store');
    }

    public function history(Request $request): JsonResponse
    {
        $data = $request->validate([
            'metal' => 'required|in:gold,silver',
            'karat' => 'required|string|max:30',
            'range' => 'nullable|string|in:1,7,30,90,180,365,all',
        ]);

        $purity = Purity::active()->where('metal', $data['metal'])->where('karat', $data['karat'])->first();
        if (! $purity) {
            return response()->json(['rows' => [], 'total' => 0]);
        }

        return response()->json($this->rates->history($purity, $data['range'] ?? '30'));
    }

    public function saveEstimate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'karat' => 'required|string|max:30',
            'weight_grams' => 'required|numeric|min:0.001|max:100000',
            'making_percent' => 'required|numeric|min:0|max:100',
        ]);

        // Always recompute on the server using the current DB rate
        $purity = $this->rates->currentRates()
            ->where('metal', 'gold')->where('karat', $data['karat'])->first();
        if (! $purity || $purity->price <= 0) {
            return response()->json(['message' => 'Unknown purity.'], 422);
        }

        $vatPct = (float) Setting::get('vat_percent', 5);
        $base = $data['weight_grams'] * $purity->price;
        $making = $base * $data['making_percent'] / 100;
        $vat = ($base + $making) * $vatPct / 100;

        $key = $request->cookie(TrackVisit::COOKIE);
        $visitor = $key ? Visitor::where('visitor_key', $key)->first() : null;
        $estimate = SavedEstimate::create([
            'visitor_id' => $visitor?->id,
            'purity_id' => $purity->id,
            'karat' => $purity->karat,
            'weight_grams' => $data['weight_grams'],
            'rate_per_gram' => $purity->price,
            'making_percent' => $data['making_percent'],
            'vat_percent' => $vatPct,
            'base_amount' => round($base, 2),
            'making_amount' => round($making, 2),
            'vat_amount' => round($vat, 2),
            'total_amount' => round($base + $making + $vat, 2),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Estimate saved.', 'id' => $estimate->id, 'total' => (float) $estimate->total_amount]);
    }
}
