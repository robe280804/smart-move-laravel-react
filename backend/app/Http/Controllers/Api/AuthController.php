<?php

namespace App\Http\Controllers\Api;

use App\Dto\UserDto;
use App\Events\UserRegistration;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiError;
use App\Http\Responses\ApiSuccess;
use App\Models\User;
use App\Services\UserService;
use App\Services\AuthService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $authService
    ) {}


    /**
     * Register user
     * @param StoreUserRequest $request
     * @return UserResource
     */
    public function register(StoreUserRequest $request): JsonResponse
    {
        $dto = new UserDto(...$request->validated());
        $user = $this->userService->create($dto);

        // Welcome email + Verify email
        event(new UserRegistration($user));

        $tokens = $this->authService->generateTokens($user);

        return $this->sendResponseWithTokens($tokens, [
            'user' => new UserResource($user)
        ])->setStatusCode(201);
    }


    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        if (!Auth::attempt($credentials)) {
            return new ApiError(null, ' Wrong credentials', Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $tokens = $this->authService->generateTokens($user);

        return $this->sendResponseWithTokens($tokens, [
            'user' => UserResource::make($user)
        ]);
    }


    /**
     * Refresh token
     * @param Request $request refresh-token via Cookie
     * @return JsonResponse access-token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        $tokens = $this->authService->generateTokens($request->user());

        return $this->sendResponseWithTokens($tokens, [
            'user' => UserResource::make($request->user()),
        ]);
    }


    /**
     * Verify user email
     * @param string $id
     * @param string $hash
     * @return JsonResponse
     */
    public function verifyEmail(string $id, string $hash): Responsable
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return new ApiSuccess(
                data: null,
                metaData: ['message' => 'Email already verified.'],
                statusCode: Response::HTTP_OK
            );
        }

        $user->markEmailAsVerified();

        return new ApiSuccess(
            data: null,
            metaData: ['message' => 'Email successfully verified.'],
            statusCode: Response::HTTP_OK
        );
    }



    private function sendResponseWithTokens(array $tokens, $body = []): JsonResponse
    {
        $rtExpireTime = config('sanctum.rt_expiration');
        $cookie = cookie(
            'refreshToken',
            $tokens['refreshToken'],
            $rtExpireTime,  // minutes
            '/',               // path
            null,            // domain
            true,            // secure
            true,          // httpOnly
            false,              // raw
            'Strict'       // sameSite
        );

        return (new ApiSuccess(
            data: $body,
            metaData: [
                'accessToken' => $tokens['accessToken'],
                'accessTokenExpiresAt' => $tokens['accessTokenExpiresAt'],
            ],
            statusCode: Response::HTTP_OK
        ))->toResponse(request())->withCookie($cookie);
    }
}
