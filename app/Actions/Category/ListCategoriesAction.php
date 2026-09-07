<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class ListCategoriesAction
{
    public function handle(): Collection
    {
        return Category::active()
            ->ordered()
            ->withCount('meals')
            ->get();
    }
}
