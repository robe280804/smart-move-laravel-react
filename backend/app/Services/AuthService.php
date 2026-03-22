<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TokenAbility;
use App\Events\UserRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AuthService
{
    public function generateTokens(User $user): array
    {
        // Expirations
        $accessTokenExpiration = Carbon::now()->addMinutes(config('sanctum.expiration'));
        $refreshTokenExpiration = Carbon::now()->addMinutes(config('sanctum.rt_expiration'));

        // Tokens
        $accessToken = $user->createToken('access-token', [TokenAbility::ACCESS_API], $accessTokenExpiration);
        $refreshToken = $user->createToken('refresh-token', [TokenAbility::ISSUE_ACCESS_TOKEN], $refreshTokenExpiration);

        return [
            'accessToken' => $accessToken->plainTextToken,
            'refreshToken' => $refreshToken->plainTextToken,
            'accessTokenExpiresAt' => $accessTokenExpiration->toISOString(),
        ];
    }

    public function attemptLogin(array $credentials): ?User
    {
        if (! Auth::attempt($credentials)) {
            return null;
        }

        return Auth::user();
    }

    public function refreshToken(User $user): array
    {
        $user->currentAccessToken()->delete();

        return $this->generateTokens($user);
    }

    public function sendResetLink(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            Log::error('Send reset password link failed', ['email' => $email]);
        }
    }

    public function resendVerificationEmail(User $user): void
    {
        event(new UserRegistration($user));
    }

    public function resetPassword(array $credentials): string
    {
        return Password::reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete();
            }
        );
    }
}
