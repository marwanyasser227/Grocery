<?php

namespace App\Http\Controllers\Api;

use App\Actions\Review\CreateReviewAction;
use App\Actions\Review\GetMealReviewsAction;
use App\Actions\Review\GetMealReviewStatsAction;
use App\Actions\Review\GetUserReviewsAction;
use App\Actions\Review\ListReviewsAction;
use App\Actions\Review\UpdateReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\Api\ReviewResource;
use App\Models\Meal;
use App\Models\Review;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request, ListReviewsAction $action): JsonResponse
    {
        $reviews = $action->handle($request);

        return self::successResponse('Reviews retrieved successfully', [
            'reviews' => ReviewResource::collection($reviews),
            'meta' => $this->paginationMeta($reviews),
        ]);
    }

    public function store(StoreReviewRequest $request, CreateReviewAction $action): JsonResponse
    {
        try {
            $review = $action->handle($request->user(), $request->validated());
        } catch (ValidationException $e) {
            return self::errorResponse('You have already reviewed this meal', null, 400);
        }

        return self::successResponse(
            'Review submitted successfully. Waiting for admin approval.',
            new ReviewResource($review),
            201
        );
    }

    public function show(Review $review): JsonResponse
    {
        return self::successResponse(
            'Review retrieved successfully',
            new ReviewResource($review->load(['user', 'meal']))
        );
    }

    public function update(UpdateReviewRequest $request, Review $review, UpdateReviewAction $action): JsonResponse
    {
        if ($request->user()->id !== $review->user_id && ! $request->user()->is_admin) {
            return self::errorResponse('Unauthorized', null, 403);
        }

        $review = $action->handle($review, $request->validated());

        return self::successResponse('Review updated successfully', new ReviewResource($review));
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($request->user()->id !== $review->user_id && ! $request->user()->is_admin) {
            return self::errorResponse('Unauthorized', null, 403);
        }

        $review->delete();

        return self::successResponse('Review deleted successfully');
    }

    public function getMealReviews(Meal $meal, Request $request, GetMealReviewsAction $action): JsonResponse
    {
        $reviews = $action->handle($meal, $request);

        return self::successResponse('Meal reviews retrieved successfully', [
            'meal' => [
                'id' => $meal->id,
                'name' => $meal->name,
                'average_rating' => round(Review::getAverageRating($meal->id), 1),
                'total_reviews' => Review::getTotalReviews($meal->id),
            ],
            'reviews' => ReviewResource::collection($reviews),
            'meta' => $this->paginationMeta($reviews),
        ]);
    }

    public function getUserReviews(Request $request, GetUserReviewsAction $action): JsonResponse
    {
        $reviews = $action->handle($request);

        return self::successResponse('User reviews retrieved successfully', [
            'reviews' => ReviewResource::collection($reviews),
            'meta' => $this->paginationMeta($reviews),
        ]);
    }

    public function getMealReviewStats(int $mealId, GetMealReviewStatsAction $action): JsonResponse
    {
        return self::successResponse('Review stats retrieved successfully', $action->handle($mealId));
    }

    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
