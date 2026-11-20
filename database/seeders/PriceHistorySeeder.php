<?php

namespace Database\Seeders;

use App\Models\PriceHistory;
use App\Models\Purity;
use Illuminate\Database\Seeder;

/**
 * Generates ~400 days of demo price history ending today at the current rates.
 * Other karats follow the 22K series by a fixed ratio so they move together.
 */
class PriceHistorySeeder extends Seeder
{
    private const DAYS = 400;

    public function run(): void
    {
        mt_srand(2026);

        $current = [
            'gold' => ['22K' => 18655, '24K' => 19410, '21K' => 17815, '18K' => 15300, 'Traditional' => 12500],
            'silver' => ['22K' => 300, '21K' => 290, '18K' => 250, 'Traditional' => 190],
        ];

        $series = [
            'gold' => $this->walk(18655, step: 5, maxMove: 260, drift: 0.0002, changeChance: 40),
            'silver' => $this->walk(300, step: 1, maxMove: 3, drift: 0.0, changeChance: 30),
        ];

        PriceHistory::query()->delete();
        $now = now();

        foreach (Purity::all() as $purity) {
            $base = $current[$purity->metal]['22K'];
            $target = $current[$purity->metal][$purity->karat] ?? null;
            if (! $target) {
                continue;
            }
            $ratio = $target / $base;
            $step = $purity->metal === 'gold' ? 5 : 1;

            $rows = [];
            foreach ($series[$purity->metal] as $daysAgo => $price) {
                $value = $daysAgo === 0 ? $target : round($price * $ratio / $step) * $step;
                $rows[] = [
                    'purity_id' => $purity->id,
                    'price_per_gram' => $value,
                    'recorded_on' => $now->copy()->subDays($daysAgo)->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 200) as $chunk) {
                PriceHistory::insert($chunk);
            }
        }
    }

    /** Backwards random walk: index 0 = today. */
    private function walk(float $today, int $step, int $maxMove, float $drift, int $changeChance): array
    {
        $prices = [0 => $today];
        $price = $today;
        for ($d = 1; $d <= self::DAYS; $d++) {
            if (mt_rand(1, 100) <= $changeChance) {
                // walking backwards: positive bias here = prices rising over time
                $move = mt_rand(-(int) ($maxMove * 0.8), $maxMove);
                $price = $price - $move - $price * $drift;
                $price = max($today * 0.5, round($price / $step) * $step);
            }
            $prices[$d] = $price;
        }

        return $prices;
    }
}
