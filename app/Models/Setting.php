<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'site_settings';

    /** Default values used when a key has not been saved yet. */
    public const DEFAULTS = [
        'site_name' => 'BDGold',
        'site_title' => 'BD Gold Price · Today\'s Gold & Silver Rates in Bangladesh',
        'hero_title' => 'Today\'s gold & silver rates in Bangladesh',
        'hero_subtitle' => 'Live updates for 24K, 22K, 21K, 18K and traditional gold. Per gram & vhori (bhori), synced with Bangladesh Jewellers Association.',
        'meta_description' => 'Today\'s gold and silver price in Bangladesh per gram and vhori, with price history, chart, jewelry calculator and Gulf comparison.',
        'rates_source' => 'BAJUS',
        'usd_bdt' => '121.40',
        'gold_int_usd' => '2748',
        'silver_int_usd' => '32.18',
        'vat_percent' => '5',
        'default_making_percent' => '6',
        'vhori_grams' => '11.664',
        'compare_note' => 'Up to 100g gold jewelry duty-free for travelers to Bangladesh.',
        'footer_text' => 'Informational only, not financial advice.',
        'contact_email' => '',
        'contact_phone' => '',

        // Live rate auto-update (goldr.org)
        'auto_update_enabled' => '1',
        'auto_update_interval' => '30',          // minutes between fetches
        'auto_update_override_manual' => '0',    // 1 = live price replaces today's manual price
        'auto_update_usd' => '1',                // also update USD/BDT from the source
        'auto_update_max_jump' => '20',          // reject a change bigger than this %
        'live_refresh_seconds' => '120',         // how often an open page re-checks prices
        'auto_last_fetch_at' => '',
        'auto_last_status' => '',
        'auto_last_message' => '',
    ];

    public static function all_settings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                $saved = static::query()->pluck('value', 'key')->toArray();
            } catch (\Throwable) {
                $saved = [];
            }

            return array_merge(self::DEFAULTS, $saved);
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all_settings()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }
}
