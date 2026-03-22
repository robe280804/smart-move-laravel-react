<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\UserDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $perPage = min((int) $request->query('per_page', 15), 100);

        return UserResource::collection($this->userService->paginateWithSubscriptions($perPage));
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);

        $dto = new UserDto(...$request->validated());
        $user = $this->userService->update($user, $dto);

        return new UserResource($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($user);

        return response()->json(null, 204);
    }
}
