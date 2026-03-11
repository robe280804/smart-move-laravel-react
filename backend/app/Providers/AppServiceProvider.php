<?php

namespace App\Providers;

use App\Enums\TokenAbility;
use App\Neuron\Nodes\CollectUserInfosNode;
use App\Repositories\Contracts\FitnessInfoRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\FitnessInfoRepository;
use App\Repositories\UserRepository;
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

        $this->app->bind(CollectUserInfosNode::class, function ($app) {
            return new CollectUserInfosNode(
                $app->make(FitnessInfoRepositoryInterface::class),
                $app->make(UserRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->overrideSanctumConfigurationToSupportRefreshToken();
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
