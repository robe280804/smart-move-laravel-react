<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\FitnessInfoDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFitnessInfoRequest;
use App\Http\Requests\UpdateFitnessInfoRequest;
use App\Http\Resources\FitnessInfoResource;
use App\Http\Responses\ApiError;
use App\Http\Responses\ApiSuccess;
use App\Models\FitnessInfo;
use App\Services\FitnessInfoService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FitnessInfoController extends Controller
{
    public function __construct(
        private readonly FitnessInfoService $fitnessInfoService,
    ) {}

    public function index(Request $request): Responsable
    {
        $fitnessInfo = $this->fitnessInfoService->findByUser($request->user());

        if ($fitnessInfo === null) {
            return new ApiError(null, 'Fitness info not found.', Response::HTTP_NOT_FOUND);
        }

        return new ApiSuccess(
            data: new FitnessInfoResource($fitnessInfo),
            metaData: [],
            statusCode: Response::HTTP_OK,
        );
    }

    public function store(StoreFitnessInfoRequest $request): Responsable
    {
        $this->authorize('create', FitnessInfo::class);

        $dto = FitnessInfoDto::from($request->validated());
        $fitnessInfo = $this->fitnessInfoService->create($request->user(), $dto);

        return new ApiSuccess(
            data: new FitnessInfoResource($fitnessInfo),
            metaData: [],
            statusCode: Response::HTTP_CREATED,
        );
    }

    public function show(FitnessInfo $fitnessInfo): Responsable
    {
        $this->authorize('view', $fitnessInfo);

        return new ApiSuccess(
            data: new FitnessInfoResource($fitnessInfo),
            metaData: [],
            statusCode: Response::HTTP_OK,
        );
    }

    public function update(UpdateFitnessInfoRequest $request, FitnessInfo $fitnessInfo): Responsable
    {
        $this->authorize('update', $fitnessInfo);

        $dto = FitnessInfoDto::from($request->validated());
        $fitnessInfo = $this->fitnessInfoService->update($fitnessInfo, $dto);

        return new ApiSuccess(
            data: new FitnessInfoResource($fitnessInfo),
            metaData: [],
            statusCode: Response::HTTP_OK,
        );
    }

    public function destroy(FitnessInfo $fitnessInfo): Responsable
    {
        $this->authorize('delete', $fitnessInfo);

        $this->fitnessInfoService->delete($fitnessInfo);

        return new ApiSuccess(
            data: null,
            metaData: ['message' => 'Fitness info deleted successfully.'],
            statusCode: Response::HTTP_NO_CONTENT,
        );
    }
}
