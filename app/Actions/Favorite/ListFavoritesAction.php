<?php

namespace App\Actions\Favorite;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListFavoritesAction
{
    public function handle(User $user): Collection
    {
        return $user->favorites()
            ->with(['meal.category', 'meal.subcategory'])
            ->latest()
            ->get();
    }
}
