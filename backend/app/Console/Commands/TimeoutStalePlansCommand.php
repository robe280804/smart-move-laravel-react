<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\WorkoutPlanStatus;
use App\Models\WorkoutPlan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('plans:timeout-stale')]
#[Description('Mark workout plans stuck in pending/processing for over 10 minutes as failed')]
class TimeoutStalePlansCommand extends Command
{
    public function handle(): int
    {
        $count = WorkoutPlan::query()
            ->whereIn('status', [WorkoutPlanStatus::Pending, WorkoutPlanStatus::Processing])
            ->where('created_at', '<', now()->subMinutes(10))
            ->update([
                'status' => WorkoutPlanStatus::Failed,
                'failure_reason' => 'generation_timeout',
            ]);

        $this->info("Timed out {$count} stale workout plan(s).");

        return self::SUCCESS;
    }
}
