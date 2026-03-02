<?php

namespace App\Services;

use App\Enums\TokenAbility;
use App\Models\User;
use Carbon\Carbon;

class AuthService
{

    public function generateTokens(User $user)
    {
        $accessTokenExpiration = Carbon::now()->addMinutes(config('sanctum.expiration'));
        $refreshTokenExpiration = Carbon::now()->addMinutes(config('sanctum.rt_expiration'));

        // Access token and refresh token
        $accessToken = $user->createToken('access-token', [TokenAbility::ACCESS_API], $accessTokenExpiration);
        $refreshToken = $user->createToken('refresh-token', [TokenAbility::ISSUE_ACCESS_TOKEN], $refreshTokenExpiration);

        return [
            'accessToken' => $accessToken->plainTextToken,
            'refreshToken' => $refreshToken->plainTextToken,
        ];
    }
}
