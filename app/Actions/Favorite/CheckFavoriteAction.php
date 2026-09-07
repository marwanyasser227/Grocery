<?php

namespace App\Actions\Favorite;

use App\Models\Meal;
use App\Models\User;

class CheckFavoriteAction
{
    public function handle(User $user, Meal $meal): bool
    {
        return $user->favorites()->where('meal_id', $meal->id)->exists();
    }
}
