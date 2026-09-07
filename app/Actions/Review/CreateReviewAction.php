<?php
// app/Actions/Review/CreateReviewAction.php

namespace App\Actions\Review;

use App\Models\Review;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateReviewAction
{
    public function handle(User $user, array $data): Review
    {
        if (Review::hasUserReviewed($user->id, $data['meal_id'])) {
            throw ValidationException::withMessages([
                'meal_id' => 'You have already reviewed this meal',
            ]);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'meal_id' => $data['meal_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? null,
            'is_approved' => false,
        ]);

        return $review->load(['user', 'meal']);
    }
}
