<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\FitnessInfo;
use App\Models\User;

interface FitnessInfoRepositoryInterface
{
    public function findByUser(User $user): ?FitnessInfo;

    public function findById(int $id): ?FitnessInfo;

    public function create(array $data): FitnessInfo;

    public function update(FitnessInfo $fitnessInfo, array $data): FitnessInfo;

    public function delete(FitnessInfo $fitnessInfo): void;
}
