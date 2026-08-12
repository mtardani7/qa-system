<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseApiController extends Controller
{
    protected function respondWithResource(JsonResource $resource, string $message = 'Request completed successfully.', int $status = 200): JsonResponse
    {
        return $resource->additional(['success' => true, 'message' => $message])->response()->setStatusCode($status);
    }

    protected function respondSuccess(mixed $data = null, string $message = 'Request completed successfully.', int $status = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $status);
    }
}
