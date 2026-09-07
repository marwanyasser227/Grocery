<?php
// app/Actions/Review/UpdateReviewAction.php

namespace App\Actions\Review;

use App\Models\Review;

class UpdateReviewAction
{
    public function handle(Review $review, array $data): Review
    {
        $review->update($data);

        return $review->load(['user', 'meal']);
    }
}
