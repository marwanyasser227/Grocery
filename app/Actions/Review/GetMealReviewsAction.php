<?php
// app/Actions/Review/GetMealReviewsAction.php

namespace App\Actions\Review;

use App\Models\Meal;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GetMealReviewsAction
{
    public function handle(Meal $meal, Request $request): LengthAwarePaginator
    {
        return Review::with('user')
            ->where('meal_id', $meal->id)
            ->approved()
            ->latest()
            ->paginate($request->input('per_page', 10));
    }
}
