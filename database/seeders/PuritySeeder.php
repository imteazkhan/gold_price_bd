<?php

namespace Database\Seeders;

use App\Models\Purity;
use Illuminate\Database\Seeder;

class PuritySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['gold', '24K', '24K Gold', 99.90, 'Purest form', '999.9 purity — nearly 100% gold. Very soft, so not ideal for daily jewelry. Best for investment bars, coins, and long-term saving.', true, false, true],
            ['gold', '22K', '22K Gold', 91.60, 'Most popular', '916 purity — the standard for Bangladeshi jewelry. Durable enough for everyday wear while keeping a rich gold color. Used in chains, bangles, rings.', true, true, true],
            ['gold', '21K', '21K Gold', 87.50, 'Balanced', '875 purity — a good middle ground. Slightly harder than 22K, popular for intricate designs and traditional ornaments that need more strength.', false, true, true],
            ['gold', '18K', '18K Gold', 75.00, 'Hardest', '750 purity — mixed with other metals for strength. Best for diamond-studded jewelry, watches, and modern designs. Less yellow, more durable.', false, true, true],
            ['gold', 'Traditional', 'Traditional Gold', null, 'Sanatan method', 'Used gold bought back or exchanged. Purity varies, so jewelers melt and refine it. Rates are lower because refining cost is deducted.', false, true, false],
            ['silver', '22K', '22K Silver', null, 'Affordable metal', 'Silver is much cheaper than gold. 22K is standard for ornaments, 18K for export items. Priced per gram and per vhori just like gold.', true, true, false],
            ['silver', '21K', '21K Silver', null, null, null, false, true, false],
            ['silver', '18K', '18K Silver', null, null, null, false, true, false],
            ['silver', 'Traditional', 'Traditional Silver', null, null, null, false, true, false],
        ];

        foreach ($rows as $i => [$metal, $karat, $label, $pct, $tagline, $desc, $featured, $table, $calc]) {
            Purity::updateOrCreate(['metal' => $metal, 'karat' => $karat], [
                'label' => $label,
                'purity_percent' => $pct,
                'tagline' => $tagline,
                'description' => $desc,
                'is_featured' => $featured,
                'show_in_table' => $table,
                'show_in_calculator' => $calc,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}
