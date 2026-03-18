<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Responses\ApiSuccess;
use App\Services\UserService;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class ChangePasswordController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function __invoke(ChangePasswordRequest $request): Responsable
    {
        $currentTokenId = $request->user()->currentAccessToken()->id ?: null;

        $this->userService->changePassword(
            $request->user(),
            $request->validated('password'),
            $currentTokenId,
        );

        return new ApiSuccess(
            data: null,
            metaData: ['message' => 'Password changed successfully.'],
            statusCode: Response::HTTP_OK,
        );
    }
}
