<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

//! request
use App\Http\Requests\Dashboard\DashboardRequest;

//! service
use App\Services\DashboardService;

//! trait
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{

    use ApiResponse;
    public function index(
        DashboardRequest $request,
        DashboardService $dashboardService
    ): JsonResponse {
        $dashboard = $dashboardService->getDashboard(
            $request->user()
        );

        return self::success(
            'Dashboard data retrieved successfully',
            $dashboard,
            200
        );
    }
}