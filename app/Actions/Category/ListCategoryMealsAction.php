<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListCategoryMealsAction
{
    private const ALLOWED_SORT_FIELDS = ['created_at', 'price', 'rating', 'title', 'sold_count'];

    public function handle(Category $category, Request $request): LengthAwarePaginator
    {
        $query = $category->meals()->with(['subcategory'])->available();

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        return $query->paginate($perPage);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->has('featured')) {
            $request->boolean('featured') ? $query->featured() : $query->where('is_featured', false);
        }

        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->input('subcategory_id'));
        }

        if ($request->has('in_stock')) {
            $request->boolean('in_stock') ? $query->inStock() : $query->outOfStock();
        }
    }

    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }

        if (! in_array($sortBy, self::ALLOWED_SORT_FIELDS)) {
            $query->orderBy('created_at', 'desc');
            return;
        }

        $sortBy === 'price'
            ? $query->orderByRaw("COALESCE(discount_price, price) {$sortOrder}")
            : $query->orderBy($sortBy, $sortOrder);
    }
}
