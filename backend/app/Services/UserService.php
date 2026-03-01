<?php

namespace App\Services;

use App\Dto\UserDto;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function create(UserDto $dto): User
    {
        return $this->userRepository->create([
            'name' => $dto->name,
            'surname' => $dto->surname,
            'email' => $dto->email,
            'password' => $dto->password,
        ]);
    }

    public function update(User $user, UserDto $dto): User
    {
        $data = array_filter([
            'name' => $dto->name,
            'surname' => $dto->surname,
            'email' => $dto->email,
            'password' => $dto->password,
        ], fn ($value) => $value !== null);

        return $this->userRepository->update($user, $data);
    }

    public function delete(User $user): void
    {
        $this->userRepository->delete($user);
    }
}
