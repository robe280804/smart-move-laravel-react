<?php

namespace App\Services;

use App\Enums\TokenAbility;
use App\Models\User;
use Carbon\Carbon;

class AuthService
{
    public function generateTokens(User $user): array
    {
        $accessTokenExpiration = Carbon::now()->addMinutes(config('sanctum.expiration'));
        $refreshTokenExpiration = Carbon::now()->addMinutes(config('sanctum.rt_expiration'));

        $accessToken = $user->createToken('access-token', [TokenAbility::ACCESS_API], $accessTokenExpiration);
        $refreshToken = $user->createToken('refresh-token', [TokenAbility::ISSUE_ACCESS_TOKEN], $refreshTokenExpiration);

        return [
            'accessToken' => $accessToken->plainTextToken,
            'refreshToken' => $refreshToken->plainTextToken,
            'accessTokenExpiresAt' => $accessTokenExpiration->toISOString(),
        ];
    }
}
