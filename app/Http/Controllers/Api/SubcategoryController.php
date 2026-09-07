<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Actions\Subcategory\ListSubcategoriesAction;
use App\Http\Resources\SubcategoryListResource;
use App\Http\Resources\SubcategoryResource;
use App\Actions\Subcategory\GetSubcategoryDetailsAction;
use App\Actions\Subcategory\ListSubcategoryMealsAction;
use App\Http\Resources\SubcategoryMealResource;

use App\Traits\ApiResponseTrait;

class SubcategoryController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get all subcategories
     */
    public function index(Request $request, ListSubcategoriesAction $action): JsonResponse
    {
        try {
            return $this->success(
                SubcategoryListResource::collection($action->handle($request)),
                'Subcategories retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve subcategories', 500, $e->getMessage());
        }
    }
    /**
     * Get single subcategory
     */
    public function show(Subcategory $subcategory, GetSubcategoryDetailsAction $action): JsonResponse
    {
        try {
            return $this->success(
                new SubcategoryResource($action->handle($subcategory)),
                'Subcategory retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve subcategory', 500, $e->getMessage());
        }
    }
    /**
     * Get meals by subcategory (paginated)
     */
    public function meals(Subcategory $subcategory, Request $request, ListSubcategoryMealsAction $action): JsonResponse
    {
        try {
            $paginator = $action->handle($subcategory, $request);
            $total = $paginator->total();

            return $this->success([
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                ],
                'meals' => SubcategoryMealResource::collection($paginator->items()),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $total,
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'empty_message' => $total === 0
                    ? 'No products match the applied filters. Try adjusting your filters.'
                    : null,
            ], $total === 0 ? 'No products match your filters.' : 'Meals retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve meals', 500, $e->getMessage());
        }
    }
}
