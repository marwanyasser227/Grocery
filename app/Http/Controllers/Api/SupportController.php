<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

//! action
use App\Actions\Support\SubmitSupportReport;
//! requests
use App\Http\Requests\Support\StoreSupportReportRequest;
//! resource
use App\Http\Resources\Api\SupportReportResource;
//! trait
use App\Traits\ApiResponse;

class SupportController extends Controller
{
    use ApiResponse;
    public function store(
        StoreSupportReportRequest $request,
        SubmitSupportReport $action
    ): JsonResponse {
        $report = $action->handle(
            $request->user(),
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return self::success(
            'Support report submitted successfully',
            new SupportReportResource($report),
            201
        );
    }
}
