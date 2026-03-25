<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\SecurityEventType;
use App\Enums\WorkoutPlanStatus;
use App\Events\SecurityAlert;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\WorkoutGenerationService;
use App\Services\WorkoutPlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\InsufficientCreditsException;

class GenerateWorkoutPlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum seconds the job may run before it is killed.
     * The Anthropic call alone can take up to 10 minutes for a complex plan.
     */
    public int $timeout = 600;

    /**
     * No automatic retries — AI generation is expensive and the failure is
     * almost never transient. The client sees WorkoutPlanStatus::Failed and
     * can trigger a new request.
     */
    public int $tries = 1;

    /**
     * @param  array<string, mixed>  $workflowState  Serialisable state bag built by the controller.
     */
    public function __construct(
        private readonly WorkoutPlan $plan,
        private readonly User $user,
        private readonly array $workflowState,
    ) {}

    public function handle(WorkoutPlanService $service, WorkoutGenerationService $generationService): void
    {
        $this->plan->update(['status' => WorkoutPlanStatus::Processing]);

        $jsonResponse = $generationService->generate($this->user, $this->workflowState);

        $service->fillFromAgentResponse($this->plan, $jsonResponse);

        Log::info('GenerateWorkoutPlanJob completed', ['plan_id' => $this->plan->id]);
    }

    public function failed(\Throwable $e): void
    {
        $isCreditsExhausted = $e instanceof InsufficientCreditsException;

        $this->plan->update([
            'status' => WorkoutPlanStatus::Failed,
            'failure_reason' => $isCreditsExhausted ? 'credits_exhausted' : 'generation_error',
        ]);

        Log::error('GenerateWorkoutPlanJob failed', [
            'plan_id' => $this->plan->id,
            'error' => $e->getMessage(),
        ]);

        if ($isCreditsExhausted) {
            event(new SecurityAlert(
                type: SecurityEventType::AiCreditsExhausted,
                ip: 'queue',
                userId: $this->user->id,
                details: "Workout plan {$this->plan->id} failed: AI provider has insufficient credits. Top up your API key.",
            ));

            return;
        }

        event(new SecurityAlert(
            type: SecurityEventType::AiGenerationFailure,
            ip: 'queue',
            userId: $this->user->id,
            details: "Workout plan {$this->plan->id} generation failed: {$e->getMessage()}",
        ));
    }
}
