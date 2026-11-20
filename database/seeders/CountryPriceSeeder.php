<?php

namespace Database\Seeders;

use App\Models\CountryPrice;
use Illuminate\Database\Seeder;

class CountryPriceSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            // country, flag, currency, 24K local price/g, FX to BDT
            ['Kuwait', '🇰🇼', 'KWD', 39.00, 401],
            ['UAE', '🇦🇪', 'AED', 467.00, 34],
            ['Bahrain', '🇧🇭', 'BHD', 48.00, 331],
            ['Saudi Arabia', '🇸🇦', 'SAR', 482.00, 33],
            ['Qatar', '🇶🇦', 'QAR', 468.00, 34],
            ['Oman', '🇴🇲', 'OMR', 50.00, 325],
        ];

        CountryPrice::query()->delete();

        foreach (['24K' => 1, '22K' => 0.9167, '21K' => 0.875] as $karat => $ratio) {
            foreach ($countries as $i => [$country, $flag, $currency, $price, $fx]) {
                CountryPrice::create([
                    'country' => $country,
                    'flag' => $flag,
                    'currency' => $currency,
                    'karat' => $karat,
                    'local_price' => round($price * $ratio, 2),
                    'fx_to_bdt' => $fx,
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
