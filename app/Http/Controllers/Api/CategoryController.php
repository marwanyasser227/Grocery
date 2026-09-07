<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Actions\Category\ListCategoriesAction;
use App\Http\Resources\Api\CategoryResource;
use App\Actions\Category\GetCategoryDetailsAction;
use App\Http\Resources\Api\MealResource;
use App\Actions\Category\ListCategoryMealsAction;

use App\Traits\ApiResponseTrait;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    /**
     * Get all categories
     */
    public function index(ListCategoriesAction $action): JsonResponse
    {
        try {
            return $this->success(
                CategoryResource::collection($action->handle()),
                'Categories retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve categories', 500, $e->getMessage());
        }
    }

    /**
     * Get single category with meals
     */
    public function show(Category $category, GetCategoryDetailsAction $action): JsonResponse
    {
        try {
            $category = $action->handle($category);

            return $this->success([
                ...CategoryResource::make($category)->resolve(),
                'meals' => MealResource::collection($category->meals),
            ], 'Category retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve category', 500, $e->getMessage());
        }
    }

    /**
     * Get meals by category (paginated)
     */
    public function meals(Category $category, Request $request, ListCategoryMealsAction $action): JsonResponse
    {
        try {
            $paginator = $action->handle($category, $request);
            $total = $paginator->total();

            return $this->success([
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'meals' => MealResource::collection($paginator->items()),
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
