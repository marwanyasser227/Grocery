<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{


    public static function success(string $message = "successfully",JsonResource|Collection|array|null $data = null, int $status = 200): JsonResponse
    {

        return response()->json([
            "success" => true,
            "message" => $message,
            "data" => $data,

        ], $status);
    }


    public static function error(string $message = "Failed", ?\Throwable $exceptionDetails = null ,  int $status = 402): JsonResponse
    {

        return response()->json([
            "success" => false,
            "message" => $message,
            "error" => $exceptionDetails?->getMessage(),

        ], $status);
    }
}
