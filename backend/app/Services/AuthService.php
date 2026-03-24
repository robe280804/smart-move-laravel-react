<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TokenAbility;
use App\Events\UserRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $user->tokens()->delete();

        return $this->generateTokens($user);
    }

    public function sendResetLink(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            Log::error('Send reset password link failed');
        }
    }

    public function isLockedOut(string $email): bool
    {
        return Cache::has($this->lockoutKey($email));
    }

    public function recordFailedAttempt(string $email): void
    {
        $attemptsKey = $this->attemptsKey($email);
        $attempts = (int) Cache::get($attemptsKey, 0) + 1;

        Cache::put($attemptsKey, $attempts, now()->addMinutes(30));

        if ($attempts >= 5) {
            Cache::put($this->lockoutKey($email), true, now()->addMinutes(15));
            Cache::forget($attemptsKey);
        }
    }

    public function clearFailedAttempts(string $email): void
    {
        Cache::forget($this->attemptsKey($email));
        Cache::forget($this->lockoutKey($email));
    }

    private function lockoutKey(string $email): string
    {
        return 'login_locked:'.hash('sha256', $email);
    }

    private function attemptsKey(string $email): string
    {
        return 'login_attempts:'.hash('sha256', $email);
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
