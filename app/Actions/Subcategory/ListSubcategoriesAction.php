<?php

namespace App\Actions\Subcategory;

use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class ListSubcategoriesAction
{
    public function handle(Request $request): Collection
    {
        $query = Subcategory::with('category')->active();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        return $query->inRandomOrder()->get();
    }
}
