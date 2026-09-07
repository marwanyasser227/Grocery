<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentService
{
    public function getPaymentHistory(User $user): Collection
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->with(['items.meal.category', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getReceiptForUser(Order $order, User $user): Order
    {
        $this->ensureOrderBelongsToUser($order, $user);

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address', 'user']);

        return $order;
    }

    public function generateInvoicePdf(Order $order, User $user)
    {
        $this->ensureOrderBelongsToUser($order, $user);

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address', 'user']);

        return Pdf::loadView('invoices.show', ['order' => $order]);
    }

    private function ensureOrderBelongsToUser(Order $order, User $user): void
    {
        if ($order->user_id !== $user->id) {
            throw new ModelNotFoundException('Order not found');
        }
    }
}
