<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetBearerTokenFromCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $refreshToken = $request->cookie('refreshToken');

        if ($refreshToken) {
            $request->headers->set('Authorization', 'Bearer ' . $refreshToken);
        }

        return $next($request);
    }
}
