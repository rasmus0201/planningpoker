<?php

namespace App\Http;

use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        int $statusCode = JsonResponse::HTTP_OK
    ): JsonResponse {
        return self::response('Success', $data, $statusCode);
    }

    public static function error(
        ?string $message = '',
        mixed $data = null,
        int $statusCode = JsonResponse::HTTP_BAD_REQUEST
    ): JsonResponse {
        return self::response($message, $data, $statusCode);
    }

    public static function deleted(): JsonResponse
    {
        return self::success(null, JsonResponse::HTTP_NO_CONTENT);
    }

    public static function created(
        mixed $data = null,
    ): JsonResponse {
        return self::success($data, JsonResponse::HTTP_CREATED);
    }

    public static function unauthorized(?string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, null, JsonResponse::HTTP_UNAUTHORIZED);
    }

    public static function forbidden(?string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, null, JsonResponse::HTTP_FORBIDDEN);
    }

    public static function notFound(?string $message = 'Not Found'): JsonResponse
    {
        return self::error($message, null, JsonResponse::HTTP_NOT_FOUND);
    }

    public static function badRequest(?string $message = 'Bad Request'): JsonResponse
    {
        return self::error($message, null, JsonResponse::HTTP_BAD_REQUEST);
    }

    public static function serverError(?string $message = 'Server Error'): JsonResponse
    {
        return self::error($message, null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function validationError(ValidationException $e): JsonResponse
    {
        return self::error('Validation Error', ['errors' => $e->errors()]);
    }

    private static function response(
        ?string $message,
        mixed $data = null,
        int $statusCode
    ): JsonResponse {
        // Make sure there is 1 and only 1 data key in response.
        if (!is_array($data) || (is_array($data) && !isset($data['data']))) {
            $data = ['data' => $data];
        }

        // Automatically use "Resources" for models/model collections
        if ($dataNested = $data['data']) {
            $data['data'] = self::getTransformedData($dataNested);
        }

        // JSON response
        return response()->json(
            [
                'message' => $message,
                ...$data
            ],
            $statusCode
        );
    }

    private static function getTransformedData(mixed $data): mixed
    {
        if (is_object($data) && is_subclass_of($data, Model::class)) {
            return self::getTransformedModel($data);
        }

        if ($data instanceof Collection) {
            return self::getTransformedModelCollection($data);
        }

        return $data;
    }

    private static function getTransformedModel(Model $model): mixed
    {
        if (!property_exists($model, 'resource')) {
            return $model;
        }

        $modelResource = $model->resource;

        return new $modelResource($model);
    }

    private static function getTransformedModelCollection(Collection $collection): mixed
    {
        if ($collection->count() === 0) {
            return $collection;
        }

        $model = $collection[0];
        if (!property_exists($model, 'resource')) {
            return $collection;
        }

        $modelResource = $model->resource;
        $transformedCollection = $modelResource::collection($collection);

        return $transformedCollection->response()->getData(true)['data'];
    }
}
