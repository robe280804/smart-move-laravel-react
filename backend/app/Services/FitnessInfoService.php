<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\FitnessInfoDto;
use App\Models\FitnessInfo;
use App\Models\User;
use App\Repositories\Contracts\FitnessInfoRepositoryInterface;

class FitnessInfoService
{
    public function __construct(
        private readonly FitnessInfoRepositoryInterface $fitnessInfoRepository,
    ) {}

    public function findByUser(User $user): ?FitnessInfo
    {
        return $this->fitnessInfoRepository->findByUser($user);
    }

    public function create(User $user, FitnessInfoDto $dto): FitnessInfo
    {
        return $this->fitnessInfoRepository->create([
            'user_id' => $user->id,
            'height' => $dto->height,
            'weight' => $dto->weight,
            'age' => $dto->age,
            'gender' => $dto->gender,
            'experience_level' => $dto->experience_level,
        ]);
    }

    public function update(FitnessInfo $fitnessInfo, FitnessInfoDto $dto): FitnessInfo
    {
        $data = array_filter([
            'height' => $dto->height,
            'weight' => $dto->weight,
            'age' => $dto->age,
            'gender' => $dto->gender,
            'experience_level' => $dto->experience_level,
        ], fn ($value) => $value !== null);

        return $this->fitnessInfoRepository->update($fitnessInfo, $data);
    }

    public function delete(FitnessInfo $fitnessInfo): void
    {
        $this->fitnessInfoRepository->delete($fitnessInfo);
    }
}
