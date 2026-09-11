<?php

namespace App\Console\Commands;

use App\Mail\IntegrationSyncFailedMail;
use App\Models\IntegrationIdMapping;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Epic 0 / 0.4 — "рассинхронизация не остаётся незамеченной". A failed leg
 * that a human hasn't noticed (job alert missed, mail bounced, whatever) is
 * still sitting in integration_id_mappings with onec_status = 'failed', or
 * stuck 'pending' well past when it should have synced — this sweeps for
 * both and alerts once a day, independent of the per-job retry alert.
 * Schedule via app/Console/Kernel.php.
 *
 * 2026-09-11 — dropped the Bitrix24 leg (bitrix_status), cancelled by the
 * client; 1С is now the only thing this checks.
 */
class CheckIntegrationDesync extends Command
{
    protected $signature = 'integration:check-desync {--stuck-minutes=60}';
    protected $description = 'Report orders whose 1С sync is failed or stuck pending';

    public function handle(): int
    {
        $stuckSince = now()->subMinutes((int) $this->option('stuck-minutes'));

        $problems = IntegrationIdMapping::query()
            ->where('onec_status', 'failed')
            ->orWhere(function ($q) use ($stuckSince) {
                $q->where('onec_status', 'pending')->where('created_at', '<', $stuckSince);
            })
            ->get();

        if ($problems->isEmpty()) {
            $this->info('No desync found.');
            return self::SUCCESS;
        }

        $this->warn("{$problems->count()} order(s) not fully synced:");
        foreach ($problems as $p) {
            $this->line("  order #{$p->orders_id}: 1С={$p->onec_status} last_error=" . ($p->last_error ?? '-'));
        }

        Log::warning('integration:check-desync found unsynced orders', [
            'orders_ids' => $problems->pluck('orders_id')->all(),
        ]);

        $alertTo = config('services.integration.alert_email');
        if ($alertTo) {
            $summary = $problems->map(fn ($p) => "#{$p->orders_id} (1С={$p->onec_status})")->implode(', ');
            Mail::to($alertTo)->send(new IntegrationSyncFailedMail(0, "Ежедневная проверка рассинхронизации: {$summary}"));
        }

        return self::SUCCESS;
    }
}
