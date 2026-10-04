<?php

namespace App\Services;

use App\Models\Order;

class OrderNumberService
{
    /**
     * Generate nomor order unik berikutnya untuk user tertentu.
     * Format: ORD-0001
     */
    public function generate(int $userId): string
    {
        $highestSequence = Order::where('user_id', $userId)
            ->where('order_number', 'like', 'ORD-%')
            ->pluck('order_number')
            ->map(function (string $orderNumber): int {
                return preg_match('/^ORD-(\d+)$/i', $orderNumber, $matches)
                    ? (int) $matches[1]
                    : 0;
            })
            ->max() ?? 0;

        $next = $highestSequence + 1;

        return 'ORD-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
