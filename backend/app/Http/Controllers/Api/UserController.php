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
use Carbon\Carbon;
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
    public function store(StoreUserRequest $request): JsonResponse
    {
        $dto = new UserDto(...$request->validated());
        $user = $this->userService->create($dto);

        // Welcome email + Verify email
        event(new UserRegistration($user));

        // Access token and refresh token
        $accessToken = $user->createToken('access-token', ['*'], Carbon::now()->addDays(1));
        $refreshToken = $user->createToken('refresh-token', ['refresh'], Carbon::now()->addDays(7));

        return (new UserResource($user))
            ->additional([
                'access_token' => $accessToken->plainTextToken,
                'access_token_expires_at' => Carbon::now()->addDays(1),
                'refresh_token' => $refreshToken->plainTextToken,
                'refresh_token_expires_at' => Carbon::now()->addDays(7)
            ])
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

    /**
     * Verify user email
     * @param string $id
     * @param string $hash
     * @return JsonResponse
     */
    public function verifyEmail(string $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email successfully verified.']);
    }
}
