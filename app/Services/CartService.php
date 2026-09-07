<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CartService
{
    public function addItem(User $user, array $data): Cart
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        $cart = $user->getOrCreateCart();

        $meal = Meal::findOrFail($data['meal_id']);

        $this->validateMeal($meal, $data['quantity']);

        return DB::transaction(function () use (
            $cart,
            $meal,
            $data,
            $maxPerProduct
        ) {
            $cartItem = $cart->items()
                ->where('meal_id', $meal->id)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $data['quantity'];

                $effectiveMax = min(
                    $maxPerProduct,
                    $meal->stock_quantity
                );

                if ($newQuantity > $effectiveMax) {
                    throw new RuntimeException(
                        "Maximum {$maxPerProduct} units per product. " .
                            "You already have {$cartItem->quantity} in cart; " .
                            "maximum total is {$effectiveMax}."
                    );
                }

                $cartItem->update([
                    'quantity' => $newQuantity,
                ]);
            } else {
                $cart->items()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $data['quantity'],
                    'unit_price' => $meal->final_price,
                    'discount_amount' => 0,
                    'subtotal' => 0,
                ]);
            }

            $cart->calculateTotals();

            $cart->load([
                'items.meal.category',
                'items.meal.subcategory',
            ]);

            return $cart;
        });
    }

    private function validateMeal(
        Meal $meal,
        int $quantity
    ): void {
        if (! $meal->is_available) {
            throw new RuntimeException(
                'This meal is currently unavailable'
            );
        }

        if (! $meal->isInStock()) {
            throw new RuntimeException(
                'This meal is out of stock'
            );
        }

        if ($meal->stock_quantity < $quantity) {
            throw new RuntimeException(
                "Only {$meal->stock_quantity} items available in stock"
            );
        }
    }

    public function updateItem(
        User $user,
        string $itemId,
        int $quantity
    ): Cart {
        $cart = $user->getOrCreateCart();

        $cartItem = $cart->items()
            ->with('meal')
            ->findOrFail($itemId);

        $meal = $cartItem->meal;

        if (! $meal->is_available) {
            throw new RuntimeException(
                'This meal is currently unavailable'
            );
        }

        if (! $meal->isInStock()) {
            throw new RuntimeException(
                'This meal is out of stock'
            );
        }

        if ($meal->stock_quantity < $quantity) {
            throw new RuntimeException(
                "Only {$meal->stock_quantity} items available in stock"
            );
        }

        return DB::transaction(function () use (
            $cart,
            $cartItem,
            $quantity
        ) {
            $cartItem->update([
                'quantity' => $quantity,
            ]);

            $cart->load([
                'items.meal.category',
                'items.meal.subcategory',
            ]);

            return $cart;
        });
    }


    public function removeItem(User $user, string $itemId): Cart
{
    $cart = $user->getOrCreateCart();

    $cartItem = $cart->items()
        ->findOrFail($itemId);

    return DB::transaction(function () use ($cart, $cartItem) {

        $cartItem->delete();
        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return $cart;
    });



}


public function clear(User $user): Cart
{
    $cart = $user->getOrCreateCart();

    return DB::transaction(function () use ($cart) {
        $cart->items()->delete();

        // Bulk delete does not trigger CartItem::deleted event
        $cart->calculateTotals();

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return $cart;
    });
}
}
