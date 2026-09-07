<?php
// app/Actions/Review/GetUserReviewsAction.php

namespace App\Actions\Review;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GetUserReviewsAction
{
    public function handle(Request $request): LengthAwarePaginator
    {
        $userId = $request->input('user_id') ?? $request->user()->id;

        // NOTE: original code has no ->approved() filter here — carried over as-is.
        // Flagged separately to raise with your mentor.
        return Review::with('meal')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($request->input('per_page', 10));
    }
}
