<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Models\User;
use App\Services\ShippingService;

class GetUserCart
{
    public function __construct(
        private ShippingService $shippingService
    ) {}

    public function handle(User $user, ?string $deliveryType): array
    {
        $cart = $user->getOrCreateCart();

        $cart->load(
            ['items.meal.category','items.meal.subcategory' ]);

        $shippingFee = null;
        $totalWithShipping = null;

        if (
            $deliveryType &&
            in_array($deliveryType, ['delivery', 'pickup'], true)
            ) {
            $shippingFee = $this->shippingService->calculateShippingFee(
                (float) $cart->subtotal,
                $deliveryType
            );

            $totalWithShipping = (float) $cart->total + $shippingFee;
        }

        return [
            'cart' => $cart,
            'shipping_fee' => $shippingFee,
            'total_with_shipping' => $totalWithShipping,
        ];
    }
}
