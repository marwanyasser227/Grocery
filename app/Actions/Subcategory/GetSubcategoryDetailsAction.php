<?php

namespace App\Actions\Subcategory;

use App\Models\Subcategory;

class GetSubcategoryDetailsAction
{
    public function handle(Subcategory $subcategory): Subcategory
    {
        return $subcategory->load(['category', 'meals' => function ($query) {
            $query->available()->limit(10);
        }]);
    }
}
