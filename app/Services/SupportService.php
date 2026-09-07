<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\SupportReport;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SupportService
{
    public function submitReport(
        User $user,
        array $data,
        ?string $ipAddress,
        ?string $userAgent
    ): SupportReport {
        $orderNumber = !empty($data['order_number'])
            ? trim((string) $data['order_number'])
            : null;

        if ($orderNumber !== null) {
            $this->validateOrderOwnership(
                $user,
                $orderNumber
            );
        }

        return SupportReport::create([
            'user_id' => $user->id,
            'issue_type' => trim($data['issue_type']),
            'order_number' => $orderNumber,
            'message' => trim($data['message']),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    private function validateOrderOwnership(
        User $user,
        string $orderNumber
    ): void {
        $exists = Order::query()
            ->where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'order_number' => [
                    'Order number not found on your account.',
                ],
            ]);
        }
    }
}