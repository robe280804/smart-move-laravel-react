<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FitnessInfo;
use App\Models\User;
use App\Repositories\Contracts\FitnessInfoRepositoryInterface;

class FitnessInfoRepository implements FitnessInfoRepositoryInterface
{
    public function findByUser(User $user): ?FitnessInfo
    {
        return FitnessInfo::query()->where('user_id', $user->id)->first();
    }

    public function findById(int $id): ?FitnessInfo
    {
        return FitnessInfo::query()->find($id);
    }

    public function create(array $data): FitnessInfo
    {
        return FitnessInfo::query()->create($data);
    }

    public function update(FitnessInfo $fitnessInfo, array $data): FitnessInfo
    {
        $fitnessInfo->update($data);

        return $fitnessInfo->fresh();
    }

    public function delete(FitnessInfo $fitnessInfo): void
    {
        $fitnessInfo->delete();
    }
}
