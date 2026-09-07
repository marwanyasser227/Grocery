<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Actions\Favorite\ListFavoritesAction;
use App\Http\Resources\FavoriteResource;
use App\Actions\Favorite\ToggleFavoriteAction;
use App\Actions\Favorite\CheckFavoriteAction;
use App\Actions\Favorite\RemoveFavoriteAction;

use App\Traits\V1\ApiResponse;

class FavoriteController extends Controller
{
    use ApiResponse;

    /**
     * Get all user's favorite meals
     */
    public function index(Request $request, ListFavoritesAction $action): JsonResponse
    {
        $favorites = FavoriteResource::collection($action->handle($request->user()));

        return self::successResponse('Favorites retrieved successfully', [
            'favorites' => $favorites,
            'total_count' => $favorites->count(),
        ]);
    }

    /**
     * Toggle favorite status for a meal
     */
    public function toggle(Request $request, Meal $meal, ToggleFavoriteAction $action): JsonResponse
    {
        $result = $action->handle($request->user(), $meal);

        return self::successResponse($result['message'], [
            'meal_id' => $meal->id,
            'is_favorited' => $result['is_favorited'],
        ]);
    }

    /**
     * Check if a meal is favorited
     */
    public function check(Request $request, Meal $meal, CheckFavoriteAction $action): JsonResponse
    {
        $isFavorited = $action->handle($request->user(), $meal);

        return self::successResponse('Favorite status retrieved', [
            'meal_id' => $meal->id,
            'is_favorited' => $isFavorited,
        ]);
    }

    /**
     * Remove meal from favorites
     */
    public function remove(Request $request, Meal $meal, RemoveFavoriteAction $action): JsonResponse
    {
        $deleted = $action->handle($request->user(), $meal);

        if (! $deleted) {
            return self::errorResponse('Meal was not in favorites', null, 404);
        }

        return self::successResponse('Removed from favorites', [
            'meal_id' => $meal->id,
            'is_favorited' => false,
        ]);
    }
}
