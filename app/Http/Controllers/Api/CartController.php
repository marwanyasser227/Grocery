<?php
declare(strict_types = 1);
namespace App\Http\Controllers\Api;

use App\Actions\Cart\GetUserCart;
use App\Http\Controllers\Controller;

//! requests
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\Api\Cart\CartResource;

//! services
use App\Services\CartService;

//! trait
use App\Traits\ApiResponse;

//! facades
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get user's cart
     */
    use ApiResponse;
    public function index(Request $request, GetUserCart $action): JsonResponse
    {
        try {
            $result =  $action->handle($request->user(), $request->query('delivery_type'));

            return self::success(
                'Cart retrieved successfully',
                [
                    'cart' => new CartResource(
                        $result['cart']
                    ),
                    'shipping_fee' => $result['shipping_fee'],
                    'total_with_shipping' => $result['total_with_shipping'],
                ],
                200,
            );
        } catch (\Exception $e) {
            return self::error(
                'Failed to load cart',
                $e,
                500,
            );
        }
    }

    /**
     * Add item to cart
     */
    public function addItem(
        AddCartItemRequest  $request,
        CartService $cartService
    ): JsonResponse {
        try {
            $cart = $cartService->addItem(
                $request->user(),
                $request->validated()
            );

            return self::success(
                'Item added to cart successfully',
                new CartResource($cart)
            );
        } catch (\Exception $e) {
            return self::error(
                'Failed to add item to cart',
                $e,
                500
            );
        }
    }
    /**
     * Update cart item quantity
     */
    public function updateItem(
        UpdateCartItemRequest $request,
        CartService $cartService,
        string $itemId
    ): JsonResponse {
        try {
            $cart = $cartService->updateItem(
                $request->user(),
                $itemId,
                $request->validated()['quantity']
            );

            return self::success(
                'Cart item updated successfully',
                new CartResource($cart)
            );
        } catch (\Exception $e) {
            return self::error(
                'Failed to update cart item',
                $e,
                500
            );
        }
    }
    /**
     * Remove item from cart
     */
    public function removeItem(
        Request $request,
        CartService $cartService,
        string $itemId
    ): JsonResponse {
        try {
            $cart = $cartService->removeItem(
                $request->user(),
                $itemId
            );

            return self::success(
                'Item removed from cart successfully',
                new CartResource($cart)
            );
        } catch (ModelNotFoundException $e) {
            return self::error(
                'Cart item not found',
                null,
                404
            );
        } catch (\Exception $e) {
            return self::error(
                'Failed to remove item from cart',
                $e,
                500
            );
        }
    }

    /**
     * Clear cart
     */
    public function clear(
        Request $request,
        CartService $cartService
    ): JsonResponse {
        try {
            $cart = $cartService->clear(
                $request->user()
            );

            return self::success(
                'Cart cleared successfully',
                new CartResource($cart)
            );
        } catch (\Exception $e) {
            return self::error(
                'Failed to clear cart',
                $e,
                500
            );
        }
    }
}
