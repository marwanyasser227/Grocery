<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddSmartListMealRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\SmartList;
use App\Services\SmartListService;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;

class AddMealToSmartListController extends Controller
{
    use ApiResponse;

    public function __invoke(
        AddSmartListMealRequest $request,
        SmartList $smart_list,
        SmartListService $smartListService
    ): JsonResponse {
        $updatedList = $smartListService->addMeal($smart_list, $request->user(), $request->validated('meal_id'));

        return $this->successResponse(
            'Item added to wish list successfully',
            new SmartListResource($updatedList)
        );
    }
}
