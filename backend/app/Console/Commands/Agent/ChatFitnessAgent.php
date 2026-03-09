<?php

declare(strict_types=1);

namespace App\Console\Commands\Agent;

use App\Neuron\FitnessAgent;
use Illuminate\Console\Command;
use NeuronAI\Chat\Messages\UserMessage;

class ChatFitnessAgent extends Command
{
    protected $signature = 'agent:chat-fitness {message : The message to send to the FitnessAgent}';

    protected $description = 'Send a message to the FitnessAgent and display the response';

    public function handle(): int
    {
        $message = $this->argument('message');

        $this->info('Sending message to FitnessAgent...');

        $response = FitnessAgent::make()
            ->chat(new UserMessage($message))
            ->getMessage();

        $this->line('');
        $this->line('<fg=green>Agent response:</fg=green>');
        $this->line($response->getContent());

        return self::SUCCESS;
    }
}
