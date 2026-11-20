<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Reads live Bangladesh gold/silver rates from goldr.org's embeddable widget
 * script (the same source used by github.com/bdgautam24-rgb/Live-Gold-price-Bangladesh).
 *
 * The script embeds the data as JS constants:
 *   const GoldrPriceTable_goldData   = [{"n":"২২ ক্যারেট সোনার দাম","bg_raw":17245,"sg_raw":14140.9,...}, ...];
 *   const GoldrPriceTable_silverData = [...];
 *   const GoldrPriceTable_usdRate    = 124.48;
 * bg_raw = price per gram, sg_raw = sell-back price per gram (18% less, BAJUS rule).
 */
class GoldrRateFetcher
{
    public const URL = 'https://www.goldr.org/price.js';

    /**
     * @return array{gold: array<string, array{gram: float, sell: ?float}>, silver: array<string, array{gram: float, sell: ?float}>, usd: ?float}
     */
    public function fetch(): array
    {
        $response = Http::timeout(10)->connectTimeout(5)->retry(2, 500, throw: false)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; GoldPriceBD/1.0)', 'Accept' => 'application/javascript,*/*'])
            ->get(self::URL);

        if (! $response->successful()) {
            throw new RuntimeException('Source returned HTTP '.$response->status());
        }

        return $this->parse($response->body());
    }

    public function parse(string $js): array
    {
        $gold = $this->extractArray($js, 'GoldrPriceTable_goldData');
        $silver = $this->extractArray($js, 'GoldrPriceTable_silverData');

        $result = [
            'gold' => $this->mapRows($gold),
            'silver' => $this->mapRows($silver),
            'usd' => preg_match('/GoldrPriceTable_usdRate\s*=\s*([0-9.]+)/', $js, $m) ? round((float) $m[1], 2) : null,
        ];

        if (empty($result['gold']) && empty($result['silver'])) {
            throw new RuntimeException('No recognisable prices found in source (format may have changed).');
        }

        return $result;
    }

    private function extractArray(string $js, string $name): array
    {
        if (! preg_match('/'.preg_quote($name, '/').'\s*=\s*(\[.*?\])\s*;/su', $js, $m)) {
            return [];
        }
        $data = json_decode($m[1], true);

        return is_array($data) ? $data : [];
    }

    /** Map rows like "২২ ক্যারেট সোনার দাম" to our karat codes. */
    private function mapRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $name = (string) ($row['n'] ?? '');
            $karat = $this->karatFromName($name);
            $gram = isset($row['bg_raw']) ? (float) $row['bg_raw'] : 0;
            if (! $karat || $gram <= 0) {
                continue;
            }
            $out[$karat] = [
                'gram' => round($gram, 2),
                'sell' => isset($row['sg_raw']) ? round((float) $row['sg_raw'], 2) : null,
            ];
        }

        return $out;
    }

    private function karatFromName(string $name): ?string
    {
        // Convert Bengali digits to ASCII so "২২" -> "22"
        $name = strtr($name, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);

        if (str_contains($name, 'সনাতন') || stripos($name, 'traditional') !== false) {
            return 'Traditional';
        }
        if (preg_match('/(\d{2})\s*(ক্যারেট|k\b|karat|carat)/iu', $name, $m)) {
            return $m[1].'K';
        }

        return null;
    }
}
