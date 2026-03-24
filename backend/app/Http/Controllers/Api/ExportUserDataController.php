<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserDataExportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ExportUserDataController extends Controller
{
    public function __construct(
        private readonly UserDataExportService $exportService,
    ) {}

    /**
     * Export all personal data for the given user (GDPR Article 20).
     *
     * Returns a JSON download with Content-Disposition: attachment so the
     * browser treats it as a file rather than rendering it inline.
     */
    public function __invoke(User $user): JsonResponse
    {
        $this->authorize('export', $user);

        $payload = $this->exportService->export($user);

        return response()
            ->json($payload, Response::HTTP_OK, [
                'Content-Disposition' => 'attachment; filename="user-data-export.json"',
            ], JSON_PRETTY_PRINT);
    }
}
