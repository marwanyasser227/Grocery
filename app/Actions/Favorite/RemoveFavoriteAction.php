<?php

namespace App\Actions\Favorite;

use App\Models\Meal;
use App\Models\User;

class RemoveFavoriteAction
{
    public function handle(User $user, Meal $meal): bool
    {
        return (bool) $user->favorites()->where('meal_id', $meal->id)->delete();
    }
}
