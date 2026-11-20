<?php

namespace App\Console\Commands;

use App\Services\RateSyncService;
use Illuminate\Console\Command;

class FetchLiveRates extends Command
{
    protected $signature = 'rates:fetch {--force : Fetch even if auto update is disabled or not due yet}';

    protected $description = 'Fetch live gold & silver rates (goldr.org) and store today\'s prices';

    public function handle(RateSyncService $sync): int
    {
        $log = $this->option('force')
            ? $sync->sync('cli', force: true)
            : $sync->syncIfDue('schedule');

        if (! $log) {
            $this->line($sync->enabled() ? 'Not due yet (or another fetch is running).' : 'Auto update is disabled. Use --force to fetch anyway.');

            return self::SUCCESS;
        }

        $this->info(strtoupper($log->status).': '.$log->message);
        foreach ($log->changes ?? [] as $c) {
            $this->line(sprintf('  %-20s %s -> %s', $c['label'], $c['old'] ?? '-', $c['new']));
        }

        return $log->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
