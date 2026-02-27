<?php

namespace App\Http\Controllers\Api;

use App\Dto\UserDto;
use App\Events\UserRegistration;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
    public function store(StoreUserRequest $request): UserResource
    {
        $dto = new UserDto(...$request->validated());
        $user = $this->userService->create($dto);

        event(new UserRegistration($user));

        return (new UserResource($user))
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

    /**
     * Verify user email
     * @param User $user
     * @return JsonResponse
     */
    public function verifyEmail(User $user): JsonResponse
    {
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email successfully verified.']);
    }
}
