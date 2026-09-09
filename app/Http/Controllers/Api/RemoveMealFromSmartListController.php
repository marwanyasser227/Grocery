<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SmartListResource;
use App\Models\Meal;
use App\Models\SmartList;
use App\Services\SmartListService;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RemoveMealFromSmartListController extends Controller
{
    use ApiResponse;

    public function __invoke(
        Request $request,
        SmartList $smart_list,
        Meal $meal,
        SmartListService $smartListService
    ): JsonResponse {
        $updatedList = $smartListService->removeMeal($smart_list, $request->user(), $meal->id);

        return $this->successResponse(
            'Item removed from wish list successfully',
            new SmartListResource($updatedList)
        );
    }
}
