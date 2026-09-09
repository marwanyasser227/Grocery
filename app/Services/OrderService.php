<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        private readonly ShippingService $shippingService
    ) {}

    public function getUserOrders(User $user): Collection
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOrderDetails(Order $order, User $user): Order
    {
        if ($order->user_id !== $user->id) {
            throw new ModelNotFoundException('Order not found');
        }

        return $order->load(['items.meal.category', 'items.meal.subcategory', 'address']);
    }

    public function createOrder(User $user, array $validated): Order
    {
        $cart = $user->activeCart()->with('items.meal')->first();

        if (! $cart || $cart->isEmpty()) {
            throw new InvalidArgumentException('Your cart is empty. Please add items to your cart before placing an order.');
        }

        $itemsResult = $this->validateAndProcessCartItems($cart->items);
        $items = $itemsResult['items'];

        $cart->calculateTotals();
        $shippingFee = $this->shippingService->calculateShippingFee((float) $cart->subtotal, $validated['delivery_type']);
        $totals = [
            'subtotal' => (float) $cart->subtotal,
            'tax' => (float) $cart->tax,
            'discount' => (float) $cart->discount,
            'shipping_fee' => $shippingFee,
            'total' => (float) $cart->subtotal + (float) $cart->tax + $shippingFee,
        ];

        DB::beginTransaction();

        try {
            $isHostedStripe = $validated['payment_method'] === 'stripe_checkout';

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $validated['delivery_type'] === 'delivery' ? $validated['address_id'] : null,
                'payment_method' => $validated['payment_method'],
                'payment_method_id' => null,
                'stripe_payment_intent_id' => null,
                'delivery_type' => $validated['delivery_type'],
                'status' => $isHostedStripe ? 'awaiting_payment' : 'placed',
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'discount' => $totals['discount'],
                'shipping_fee' => $totals['shipping_fee'],
                'total' => $totals['total'],
                'notes' => $validated['notes'] ?? null,
                'placed_at' => $isHostedStripe ? null : now(),
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'meal_id' => $item['meal']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['meal']->decrement('stock_quantity', $item['quantity']);
            }

            if ($cart) {
                $cart->items()->delete();
                $cart->update(['status' => 'completed']);
            }

            if (isset($validated['special_note_id'])) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => $validated['special_note_id'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            if (isset($validated['notes']) && ! isset($validated['special_note_id'])) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => null,
                    'notes' => $validated['notes'],
                ]);
            }

            DB::commit();

            return $order->load(['items.meal.category', 'items.meal.subcategory', 'address']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function trackOrder(User $user): ?array
    {
        $order = Order::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $order) {
            return null;
        }

        if ($order->status === 'awaiting_payment') {
            return [
                'order' => $order,
                'awaiting_payment' => true,
                'tracking' => null,
            ];
        }

        return [
            'order' => $order,
            'awaiting_payment' => false,
            'tracking' => [
                'position' => $order->status_position,
                'status' => $order->status,
                'status_description' => $order->status_description,
                'positions' => [
                    [
                        'position' => 1,
                        'status' => 'placed',
                        'label' => 'Order Placed',
                        'description' => 'Your order has been placed',
                        'completed' => in_array($order->status, ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered']),
                        'timestamp' => $order->placed_at,
                    ],
                    [
                        'position' => 2,
                        'status' => 'processing',
                        'label' => 'Processing',
                        'description' => 'Your order is being processed',
                        'completed' => in_array($order->status, ['processing', 'shipping', 'out_for_delivery', 'delivered']),
                        'timestamp' => $order->processing_at,
                    ],
                    [
                        'position' => 3,
                        'status' => 'shipping',
                        'label' => 'Shipping',
                        'description' => 'Your order is being shipped',
                        'completed' => in_array($order->status, ['shipping', 'out_for_delivery', 'delivered']),
                        'timestamp' => $order->shipping_at,
                    ],
                    [
                        'position' => 4,
                        'status' => 'out_for_delivery',
                        'label' => 'Out for Delivery',
                        'description' => 'Your order is on the way',
                        'completed' => in_array($order->status, ['out_for_delivery', 'delivered']),
                        'timestamp' => $order->out_for_delivery_at,
                    ],
                    [
                        'position' => 5,
                        'status' => 'delivered',
                        'label' => 'Delivered',
                        'description' => 'Your order has been delivered',
                        'completed' => $order->status === 'delivered',
                        'timestamp' => $order->delivered_at,
                    ],
                ],
            ],
        ];
    }

    private function validateAndProcessCartItems($cartItems): array
    {
        $items = [];
        $subtotal = 0;

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (! $meal) {
                throw new InvalidArgumentException('One or more items in your cart are no longer available.');
            }

            if (! $meal->is_available) {
                throw new InvalidArgumentException("Meal '{$meal->title}' is currently unavailable");
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                throw new InvalidArgumentException("Only {$meal->stock_quantity} items available for '{$meal->title}'");
            }

            $maxPerProduct = config('cart.max_quantity_per_product', 10);
            if ($cartItem->quantity > $maxPerProduct) {
                throw new InvalidArgumentException("Maximum {$maxPerProduct} units per product allowed. Please reduce quantity for '{$meal->title}'.");
            }

            $items[] = [
                'meal' => $meal,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'discount_amount' => $cartItem->discount_amount,
                'subtotal' => $cartItem->subtotal,
            ];

            $subtotal += $cartItem->subtotal;
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
        ];
    }
}
