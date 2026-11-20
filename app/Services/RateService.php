<?php

namespace App\Services;

use App\Models\PriceHistory;
use App\Models\Purity;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RateService
{
    public function vhoriGrams(): float
    {
        return (float) Setting::get('vhori_grams', 11.664) ?: 11.664;
    }

    /**
     * Active purities with their latest price, previous price and change.
     * Each item gets: price, previous, change, change_percent, vhori, recorded_on, updated_at.
     */
    public function currentRates(): Collection
    {
        $vhori = $this->vhoriGrams();

        return Purity::active()->ordered()->get()->map(function (Purity $purity) use ($vhori) {
            $latestTwo = $purity->histories()->orderByDesc('recorded_on')->limit(2)->get();
            $latest = $latestTwo->first();
            $previous = $latestTwo->get(1);

            $price = $latest ? (float) $latest->price_per_gram : 0.0;
            $prev = $previous ? (float) $previous->price_per_gram : $price;
            $change = $price - $prev;

            $purity->price = $price;
            $purity->previous = $prev;
            $purity->change = $change;
            $purity->change_percent = $prev > 0 ? ($change / $prev) * 100 : 0;
            $purity->vhori = round($price * $vhori, 2);
            $purity->recorded_on = $latest?->recorded_on;
            $purity->rate_updated_at = $latest?->updated_at;

            return $purity;
        });
    }

    /**
     * History rows for one purity within a range (days or "all"), newest first,
     * each with the change versus the record before it.
     */
    public function history(Purity $purity, string $range): array
    {
        $query = $purity->histories()->orderByDesc('recorded_on');
        $latest = (clone $query)->first();
        $total = (clone $query)->count();

        if (! $latest) {
            return ['rows' => [], 'total' => 0];
        }

        if ($range !== 'all') {
            $days = max(1, (int) $range);
            $cutoff = Carbon::parse($latest->recorded_on)->subDays($days - 1)->startOfDay();
            // one extra older row so the oldest visible row also has a change value
            $inRange = (clone $query)->whereDate('recorded_on', '>=', $cutoff)->get();
            $before = (clone $query)->whereDate('recorded_on', '<', $cutoff)->first();
            $rows = $before ? $inRange->concat([$before])->values() : $inRange;
        } else {
            $rows = $query->get();
            $inRange = $rows;
        }

        $out = [];
        $count = $inRange->count();
        for ($i = 0; $i < $count; $i++) {
            $row = $rows[$i];
            $older = $rows[$i + 1] ?? null;
            $price = (float) $row->price_per_gram;
            $out[] = [
                'date' => $row->recorded_on->format('M d, Y'),
                'iso' => $row->recorded_on->toDateString(),
                'price' => $price,
                'change' => $older ? round($price - (float) $older->price_per_gram, 2) : 0,
            ];
        }

        return ['rows' => $out, 'total' => $total];
    }

    /** Text for the "Copy rates" button. */
    public function copyText(Collection $rates): string
    {
        $s = Setting::all_settings();
        $lines = ["{$s['site_name']} rates ({$s['rates_source']}) — ".now()->format('M d, Y')];
        foreach (['gold', 'silver'] as $metal) {
            $list = $rates->where('metal', $metal)->where('show_in_table', true);
            if ($list->isEmpty()) {
                continue;
            }
            $lines[] = ucfirst($metal).':';
            foreach ($list as $p) {
                $lines[] = "{$p->karat}: ".bdt($p->vhori, 2).'/vhori | '.bdt($p->price).'/g';
            }
        }
        $lines[] = 'Source: '.url('/');

        return implode("\n", $lines);
    }

    /** Compact JSON payload the public page polls for live updates. */
    public function liveSnapshot(): array
    {
        $rates = $this->currentRates();
        $updated = $this->lastUpdated();

        return [
            'updated_at' => $updated?->toIso8601String(),
            'updated_label' => $updated ? ($updated->isToday() ? 'today '.$updated->format('g:i A') : $updated->format('M d, Y g:i A')) : '—',
            'usd_bdt' => (float) Setting::get('usd_bdt'),
            'copy_text' => $this->copyText($rates),
            'rates' => $rates->map(fn ($p) => [
                'id' => $p->id,
                'metal' => $p->metal,
                'karat' => $p->karat,
                'price' => $p->price,
                'vhori' => $p->vhori,
                'change' => round($p->change, 2),
                'change_percent' => round($p->change_percent, 2),
            ])->values(),
        ];
    }

    /** Last time any rate was updated. */
    public function lastUpdated(): ?Carbon
    {
        $ts = PriceHistory::max('updated_at');

        return $ts ? Carbon::parse($ts) : null;
    }
}
