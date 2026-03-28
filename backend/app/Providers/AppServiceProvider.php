<?php

namespace App\Providers;

use App\Enums\TokenAbility;
use App\Repositories\Contracts\FeedbackRepositoryInterface;
use App\Repositories\Contracts\FitnessInfoRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WorkoutPlanRepositoryInterface;
use App\Repositories\FeedbackRepository;
use App\Repositories\FitnessInfoRepository;
use App\Repositories\UserRepository;
use App\Repositories\WorkoutPlanRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(FitnessInfoRepositoryInterface::class, FitnessInfoRepository::class);
        $this->app->bind(WorkoutPlanRepositoryInterface::class, WorkoutPlanRepository::class);
        $this->app->bind(FeedbackRepositoryInterface::class, FeedbackRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiting();
        $this->overrideSanctumConfigurationToSupportRefreshToken();
    }

    private function configureRateLimiting(): void
    {
        // Public auth endpoints — keyed by IP to block brute-force attacks
        RateLimiter::for('auth', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Password reset flow — tighter because it triggers email delivery
        RateLimiter::for('password-reset', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Token refresh — called silently on every page load, but still bounded
        RateLimiter::for('token-refresh', function (Request $request): Limit {
            return Limit::perMinute(20)->by($request->user()?->id ?? $request->ip());
        });

        // General authenticated API — covers reads and standard mutations
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(120)->by($request->user()?->id ?? $request->ip());
        });

        // AI workout generation — expensive LLM call, strictly limited per user
        RateLimiter::for('ai-generation', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->user()?->id ?? $request->ip());
        });

        // Payment endpoints — prevents checkout/billing-portal spam
        RateLimiter::for('payments', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->user()?->id ?? $request->ip());
        });

        // Email resend — prevents abuse of verification email delivery
        RateLimiter::for('email-resend', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->user()?->id ?? $request->ip());
        });
    }

    private function overrideSanctumConfigurationToSupportRefreshToken(): void
    {
        Sanctum::$accessTokenAuthenticationCallback = function ($accessToken, $isValid) {
            $abilities = collect($accessToken->abilities);
            if (! empty($abilities) && $abilities[0] === TokenAbility::ISSUE_ACCESS_TOKEN->value) {
                return $accessToken->expires_at && $accessToken->expires_at->isFuture();
            }

            return $isValid;
        };

        Sanctum::$accessTokenRetrievalCallback = function ($request) {
            if (! $request->routeIs('refresh')) {
                return str_replace('Bearer ', '', $request->headers->get('Authorization'));
            }

            return $request->cookie('refreshToken') ?? '';
        };
    }
}
