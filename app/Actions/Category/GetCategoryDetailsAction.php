<?php

namespace App\Actions\Category;

use App\Models\Category;

class GetCategoryDetailsAction
{
    public function handle(Category $category): Category
    {
        return $category->load(['meals' => function ($query) {
            $query->available()->orderBy('created_at', 'desc');
        }]);
    }
}
