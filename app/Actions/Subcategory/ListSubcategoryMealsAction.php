<?php

namespace App\Actions\Subcategory;

use App\Models\Subcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListSubcategoryMealsAction
{
    private const ALLOWED_SORT_FIELDS = ['created_at', 'price', 'rating', 'title', 'sold_count'];

    public function handle(Subcategory $subcategory, Request $request): LengthAwarePaginator
    {
        $query = $subcategory->meals()->with('category')->available();

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        return $query->paginate($perPage)->withQueryString();
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->has('featured')) {
            $request->boolean('featured') ? $query->featured() : $query->where('is_featured', false);
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
