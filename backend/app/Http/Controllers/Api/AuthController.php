<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\UserDto;
use App\Events\UserRegistration;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiError;
use App\Http\Responses\ApiSuccess;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $authService
    ) {}

    public function register(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create(new UserDto(...$request->validated()));

        event(new UserRegistration($user));

        $tokens = $this->authService->generateTokens($user);

        return $this->sendResponseWithTokens($tokens, [
            'user' => new UserResource($user),
        ])->setStatusCode(201);
    }

    public function login(LoginRequest $request): JsonResponse|Responsable
    {
        $user = $this->authService->attemptLogin($request->validated());

        if (! $user) {
            return new ApiError(null, 'Wrong credentials', Response::HTTP_UNAUTHORIZED);
        }

        $tokens = $this->authService->generateTokens($user);

        return $this->sendResponseWithTokens($tokens, [
            'user' => UserResource::make($user),
        ]);
    }

    /**
     * Refresh token
     *
     * @param  Request  $request  refresh-token via Cookie
     * @return JsonResponse access-token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $tokens = $this->authService->refreshToken($request->user());

        return $this->sendResponseWithTokens($tokens, [
            'user' => UserResource::make($request->user()),
        ]);
    }

    /**
     * Perform the actual password reset using the token from the email link.
     *
     * @param  UpdatePasswordRequest  $request  token + email + password + password_confirmation
     */
    public function updatePassword(UpdatePasswordRequest $request): Responsable
    {
        $status = $this->authService->resetPassword($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            return new ApiError(null, __($status), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new ApiSuccess(
            data: null,
            metaData: ['message' => 'Password reset successfully.'],
            statusCode: Response::HTTP_OK
        );
    }

    /**
     * Verify user email.
     *
     * @return RedirectResponse
     */
    public function verifyEmail(Request $request, string $id, string $hash): Responsable|RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals(hash('sha256', $user->getEmailForVerification()), $hash)) {
            abort(Response::HTTP_FORBIDDEN, 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            if ($request->wantsJson()) {
                return new ApiSuccess(data: null, metaData: ['message' => 'Email already verified.'], statusCode: Response::HTTP_OK);
            }

            return redirect(config('app.frontend_url').'/email-verify?status=alredy');
        }

        $user->markEmailAsVerified();

        if ($request->wantsJson()) {
            return new ApiSuccess(data: null, metaData: ['message' => 'Email successfully verified.'], statusCode: Response::HTTP_OK);
        }

        return redirect(config('app.frontend_url').'/email-verify?status=success');
    }

    /**
     * Send a password reset link to the given email.
     *
     * @param  $request  email
     */
    public function resetPassword(Request $request): Responsable
    {
        $request->validate(['email' => 'required|email|max:255']);

        $this->authService->sendResetLink($request->input('email'));

        return new ApiSuccess(
            data: null,
            metaData: ['message' => 'If your email exists in our system, a reset link has been sent.'],
            statusCode: Response::HTTP_OK
        );
    }

    public function resendVerificationEmail(Request $request): Responsable
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return new ApiSuccess(
                data: null,
                metaData: ['message' => 'Email already verified.'],
                statusCode: Response::HTTP_OK
            );
        }

        $this->authService->resendVerificationEmail($user);

        return new ApiSuccess(
            data: null,
            metaData: ['message' => 'Verification email sent.'],
            statusCode: Response::HTTP_OK
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return (new ApiSuccess(
            data: null,
            metaData: ['message' => 'Logged out successfully.'],
            statusCode: Response::HTTP_OK
        ))->toResponse($request)->withCookie(cookie()->forget('refreshToken'));
    }

    private function sendResponseWithTokens(array $tokens, array $body = []): JsonResponse
    {
        $cookie = cookie(
            'refreshToken',
            $tokens['refreshToken'],
            config('sanctum.rt_expiration'),
            '/api/v1/',
            config('session.domain'),
            true,
            true,
            false,
            'Lax'
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
