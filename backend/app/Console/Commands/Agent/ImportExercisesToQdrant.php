<?php

declare(strict_types=1);

namespace App\Console\Commands\Agent;

use App\Services\WorkflowCsvToQdrant;
use Illuminate\Console\Command;

class ImportExercisesToQdrant extends Command
{
    protected $signature = 'agent:import-exercises
                            {--path= : Absolute path to the CSV file (defaults to exercise-gym-dataset.csv in project root)}';

    protected $description = 'Import exercises from CSV into Qdrant vector store';

    public function handle(WorkflowCsvToQdrant $service): int
    {
        $csvPath = $this->option('path') ?? base_path('exercise-gym-dataset.csv');

        if (! file_exists($csvPath)) {
            $this->error("CSV file not found: {$csvPath}");

            return self::FAILURE;
        }

        $this->info("Reading exercises from: {$csvPath}");

        $count = $service->run($csvPath);

        $this->info("Successfully imported {$count} exercises into Qdrant.");

        return self::SUCCESS;
    }
}
