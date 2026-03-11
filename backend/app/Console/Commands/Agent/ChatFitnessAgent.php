<?php

declare(strict_types=1);

namespace App\Console\Commands\Agent;

use App\Neuron\FitnessAgent;
use App\Neuron\FitnessAgentWorkflow;
use Illuminate\Console\Command;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Workflow\WorkflowState;

class ChatFitnessAgent extends Command
{
    protected $signature = 'agent:chat-fitness {message : The message to send to the FitnessAgent}';

    protected $description = 'Send a message to the FitnessAgent and display the response';

    public function handle(): int
    {
        $raw = (string) $this->argument('message');

        $this->info('Sending message to FitnessAgent...');

        $state = new WorkflowState();
        $state->set('user_message', $raw);

        $workflow = FitnessAgentWorkflow::make(state: $state);
        foreach ($workflow->init()->run() as $_) {
        }

        $sanitized = $state->get('user_message');

        $response = FitnessAgent::make()
            ->chat(new UserMessage($sanitized))
            ->getMessage();

        $this->line('');
        $this->line('<fg=green>Agent response:</fg=green>');
        $this->line($response->getContent());

        return self::SUCCESS;
    }
}
