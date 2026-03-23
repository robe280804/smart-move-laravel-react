<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\AdminAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function update(AdminUpdateUserRequest $request, User $user): UserResource
    {
        $this->authorize('adminUpdate', $user);

        $updatedUser = $this->userService->adminUpdate($user, $request->validated());

        event(new AdminAction(
            adminId: $request->user()->id,
            action: 'update_user',
            ip: $request->ip() ?? 'unknown',
            targetUserId: $user->id,
            details: 'Fields: '.implode(', ', array_keys($request->validated())),
        ));

        return new UserResource($updatedUser->load('subscriptions'));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('adminDelete', $user);

        event(new AdminAction(
            adminId: request()->user()->id,
            action: 'delete_user',
            ip: request()->ip() ?? 'unknown',
            targetUserId: $user->id,
            details: "Admin deleted user (email: {$user->email}).",
        ));

        $this->userService->delete($user);

        return response()->json(null, 204);
    }
}
