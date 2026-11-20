<?php

namespace App\Services;

use App\Models\PriceHistory;
use App\Models\Purity;
use App\Models\RateFetchLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Pulls live rates and writes them into today's price_histories rows.
 *
 * Rules:
 *  - Only runs when auto update is enabled (unless forced by an admin / CLI).
 *  - A price an admin entered manually for today is kept, unless
 *    "auto_update_override_manual" is switched on.
 *  - A jump larger than "auto_update_max_jump" % vs the last known price is
 *    rejected as a probable bad read.
 */
class RateSyncService
{
    public function __construct(private GoldrRateFetcher $fetcher) {}

    public function enabled(): bool
    {
        return (bool) Setting::get('auto_update_enabled');
    }

    public function intervalMinutes(): int
    {
        return max(5, (int) Setting::get('auto_update_interval', 30));
    }

    public function lastFetchAt(): ?Carbon
    {
        $v = Setting::get('auto_last_fetch_at');

        return $v ? Carbon::parse($v) : null;
    }

    public function isDue(): bool
    {
        $last = $this->lastFetchAt();

        return $this->enabled() && (! $last || $last->lte(now()->subMinutes($this->intervalMinutes())));
    }

    /** Run only if enabled and the interval has passed. */
    public function syncIfDue(string $trigger): ?RateFetchLog
    {
        return $this->isDue() ? $this->sync($trigger) : null;
    }

    public function sync(string $trigger, bool $force = false, ?int $userId = null): ?RateFetchLog
    {
        if (! $force && ! $this->enabled()) {
            return null;
        }

        // Prevent parallel fetches (several visitors at once, cron + visitor, ...)
        $lock = Cache::lock('rate-sync', 60);
        if (! $lock->get()) {
            return null;
        }

        $started = microtime(true);
        try {
            $data = $this->fetcher->fetch();
            [$status, $message, $changes] = $this->apply($data, $userId);
        } catch (\Throwable $e) {
            Log::warning('Rate sync failed: '.$e->getMessage());
            [$status, $message, $changes] = ['failed', mb_substr($e->getMessage(), 0, 480), null];
        } finally {
            $lock->release();
        }

        Setting::put([
            'auto_last_fetch_at' => now()->toDateTimeString(),
            'auto_last_status' => $status,
            'auto_last_message' => $message,
        ]);

        return RateFetchLog::create([
            'status' => $status,
            'trigger' => $trigger,
            'message' => $message,
            'changes' => $changes,
            'duration_ms' => (int) round((microtime(true) - $started) * 1000),
            'user_id' => $userId,
            'created_at' => now(),
        ]);
    }

    private function apply(array $data, ?int $userId): array
    {
        $today = now()->toDateString();
        $overrideManual = (bool) Setting::get('auto_update_override_manual');
        $maxJump = max(1, (float) Setting::get('auto_update_max_jump', 20));
        $changes = [];
        $notes = [];

        foreach (Purity::all() as $purity) {
            $live = $data[$purity->metal][$purity->karat] ?? null;
            if (! $live) {
                continue;
            }

            $todayRow = $purity->histories()->whereDate('recorded_on', $today)->first();
            $previous = $todayRow ?? $purity->histories()->orderByDesc('recorded_on')->first();
            $old = $previous ? (float) $previous->price_per_gram : null;

            if ($todayRow && $todayRow->source === 'manual' && ! $overrideManual) {
                $notes[] = "{$purity->label}: kept manual price";
                continue;
            }

            if ($old && abs($live['gram'] - $old) / $old * 100 > $maxJump) {
                $notes[] = "{$purity->label}: rejected {$live['gram']} (more than {$maxJump}% from {$old})";
                continue;
            }

            if ($todayRow && (float) $todayRow->price_per_gram === (float) $live['gram'] && $todayRow->source === 'auto') {
                continue;
            }

            PriceHistory::updateOrCreate(
                ['purity_id' => $purity->id, 'recorded_on' => $today],
                [
                    'price_per_gram' => $live['gram'],
                    'sell_price_per_gram' => $live['sell'],
                    'source' => 'auto',
                    'note' => ! empty($live['derived']) ? 'Auto: derived from 22K (goldr.org)' : 'Auto: goldr.org',
                    'updated_by' => null,
                ],
            );

            if ($old === null || (float) $old !== (float) $live['gram']) {
                $changes[] = ['label' => $purity->label, 'old' => $old, 'new' => $live['gram']];
            }
        }

        if (! empty($data['usd']) && Setting::get('auto_update_usd')) {
            Setting::put(['usd_bdt' => (string) $data['usd']]);
        }

        $status = $changes ? 'success' : 'unchanged';
        $message = $changes ? count($changes).' price(s) updated.' : 'No price changes.';
        if ($notes) {
            $message .= ' '.implode('; ', $notes);
            if (! $changes) {
                $status = 'skipped';
            }
        }

        return [$status, mb_substr($message, 0, 480), $changes ?: null];
    }
}
