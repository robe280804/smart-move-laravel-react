<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\WorkoutPlanStatus;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Neuron\FitnessAgentWorkflow;
use App\Services\WorkoutPlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\WorkflowState;

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
     * @param array<string, mixed> $workflowState Serialisable state bag built by the controller.
     */
    public function __construct(
        private readonly WorkoutPlan $plan,
        private readonly User $user,
        private readonly array $workflowState,
    ) {}

    public function handle(WorkoutPlanService $service): void
    {
        // Status update to processing
        $this->plan->update(['status' => WorkoutPlanStatus::Processing]);

        $state = new WorkflowState();

        foreach ($this->workflowState as $key => $value) {
            $state->set($key, $value);
        }

        // Workflow execute
        $workflow = FitnessAgentWorkflow::create(state: $state);
        $workflow->init()->run();

        $service->fillFromAgentResponse(
            $this->plan,
            (string) $state->get('agent_response', ''),
        );

        Log::info('GenerateWorkoutPlanJob completed', ['plan_id' => $this->plan->id]);
    }

    public function failed(\Throwable $e): void
    {
        $this->plan->update(['status' => WorkoutPlanStatus::Failed]);

        Log::error('GenerateWorkoutPlanJob failed', [
            'plan_id' => $this->plan->id,
            'error'   => $e->getMessage(),
        ]);
    }
}
