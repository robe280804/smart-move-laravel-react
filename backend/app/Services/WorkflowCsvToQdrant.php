<?php

declare(strict_types=1);

namespace App\Services;

use App\Neuron\FitnessAgentRag;
use NeuronAI\RAG\Document;

class WorkflowCsvToQdrant
{
    private const CHUNK_SIZE = 50;

    private const CATEGORY_MAP = [
        'Strength' => 'strength',
        'Cardio' => 'cardio',
        'Stretching' => 'mobility',
        'Plyometrics' => 'plyometric',
        'Olympic Weightlifting' => 'strength',
        'Powerlifting' => 'strength',
        'Strongman' => 'strength',
    ];

    private const ENERGY_SYSTEM_MAP = [
        'Strength' => 'phosphocreatine',
        'Powerlifting' => 'phosphocreatine',
        'Olympic Weightlifting' => 'phosphocreatine',
        'Strongman' => 'phosphocreatine',
        'Plyometrics' => 'phosphocreatine',
        'Cardio' => 'oxidative',
        'Stretching' => 'none',
    ];

    private const DIFFICULTY_MAP = [
        'Beginner' => 'beginner',
        'Intermediate' => 'intermediate',
        'Expert' => 'expert',
    ];

    public function run(string $csvPath): int
    {
        $documents = $this->buildDocuments($csvPath);
        $chunks = array_chunk($documents, self::CHUNK_SIZE);
        $rag = FitnessAgentRag::make();

        foreach ($chunks as $chunk) {
            $rag->addDocuments($chunk, 50);
        }

        return count($documents);
    }


    /** @return Document[] */
    private function buildDocuments(string $csvPath): array
    {
        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Cannot open CSV file: {$csvPath}");
        }

        $header = fgetcsv($handle);
        $documents = [];
        $index = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            if ($data === false) {
                continue;
            }

            $document = $this->mapRowToDocument($data, $index);
            $documents[] = $document;
            $index++;
        }

        fclose($handle);

        return $documents;
    }

    /** @param array<string, string> $data */
    private function mapRowToDocument(array $data, int $index): Document
    {
        $name = trim($data['Title'] ?? '');
        $description = trim($data['Desc'] ?? '');
        $type = trim($data['Type'] ?? '');
        $bodyPart = trim($data['BodyPart'] ?? '');
        $equipment = trim($data['Equipment'] ?? '');
        $level = trim($data['Level'] ?? '');

        $content = $this->buildContent($name, $description, $type, $bodyPart, $equipment, $level);

        $document = new Document($content);
        $document->id = $index;
        $document->sourceType = 'csv';
        $document->sourceName = 'exercise-gym-dataset';

        $document->addMetadata('name', $name);
        $document->addMetadata('category', self::CATEGORY_MAP[$type] ?? 'strength');
        $document->addMetadata('equipment', strtolower($equipment));
        $document->addMetadata('primary-muscle', strtolower($bodyPart));
        $document->addMetadata('secondary_muscle', '');
        $document->addMetadata('difficulty', self::DIFFICULTY_MAP[$level] ?? 'intermediate');
        $document->addMetadata('energy_sistem', self::ENERGY_SYSTEM_MAP[$type] ?? 'phosphocreatine');

        return $document;
    }

    private function buildContent(
        string $name,
        string $description,
        string $type,
        string $bodyPart,
        string $equipment,
        string $level,
    ): string {
        $parts = ["Exercise: {$name}"];

        if ($description !== '') {
            $parts[] = "Description: {$description}";
        }

        $parts[] = "Type: {$type}";
        $parts[] = "Target muscle: {$bodyPart}";
        $parts[] = "Equipment: {$equipment}";
        $parts[] = "Level: {$level}";

        return implode('. ', $parts);
    }
}
