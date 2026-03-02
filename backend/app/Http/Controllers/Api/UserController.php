<?php

namespace App\Http\Controllers\Api;

use App\Dto\UserDto;
use App\Events\UserRegistration;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cookie;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * Index user ( 15 pagination )
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $users = $this->userService->paginate();

        return UserResource::collection($users);
    }

    /**
     * Store user
     * @param StoreUserRequest $request
     * @return UserResource
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $dto = new UserDto(...$request->validated());
        $user = $this->userService->create($dto);

        return new UserResource($user)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show user
     * @param User $user
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * Update user
     * @param UpdateUserRequest $request
     * @param User $user
     * @return UserResource
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $dto = new UserDto(...$request->validated());
        $user = $this->userService->update($user, $dto);

        return new UserResource($user);
    }

    /**
     * Delete user
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return response()->json(null, 204);
    }
}
