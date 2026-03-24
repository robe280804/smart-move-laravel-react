<?php

namespace App\Services;

use App\Dto\UserDto;
use App\Enums\Role;
use App\Exceptions\RegistrationClosedException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    public function paginateWithSubscriptions(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginateWithSubscriptions($perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function create(UserDto $dto): User
    {
        $maxUsers = config('app.max_users');

        if ($maxUsers !== null && User::query()->count() >= $maxUsers) {
            throw new RegistrationClosedException('Registration is currently closed. We have reached our user capacity.');
        }

        $user = $this->userRepository->create([
            'name' => $dto->name,
            'surname' => $dto->surname,
            'email' => $dto->email,
            'password' => $dto->password,
        ]);

        $user->assignRole(Role::User->value);

        return $user;
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

    /**
     * Admin update: allows updating name, surname, email, and role.
     *
     * @param  array<string, mixed>  $data
     */
    public function adminUpdate(User $user, array $data): User
    {
        $role = null;

        if (array_key_exists('role', $data)) {
            $role = $data['role'];
            unset($data['role']);
        }

        if (count($data) > 0) {
            $user = $this->userRepository->update($user, $data);
        }

        if ($role !== null) {
            $user->syncRoles([$role]);
            $user->refresh();
        }

        return $user;
    }

    public function delete(User $user): void
    {
        // Cancel any Stripe subscriptions that are still billing before removing the account.
        $user->subscriptions()
            ->whereNotIn('stripe_status', ['canceled', 'incomplete_expired'])
            ->get()
            ->each(function ($subscription) use ($user): void {
                try {
                    $subscription->cancelNow();
                } catch (\Throwable $e) {
                    Log::warning("Failed to cancel Stripe subscription {$subscription->stripe_id} for user {$user->id} during account deletion: {$e->getMessage()}");
                }
            });

        $this->userRepository->delete($user);
    }

    /**
     * Update the user's password and revoke all Sanctum tokens except the current session.
     */
    public function changePassword(User $user, string $newPassword, ?int $currentTokenId): void
    {
        $this->userRepository->update($user, ['password' => $newPassword]);

        $query = $user->tokens();

        if ($currentTokenId !== null) {
            $query->where('id', '!=', $currentTokenId);
        }

        $query->delete();
    }
}
