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
        $last = Order::where('user_id', $userId)
            ->orderByDesc('id')
            ->value('order_number');

        if ($last) {
            // Ambil angka dari format ORD-XXXX
            $number = (int) substr($last, 4);
            $next   = $number + 1;
        } else {
            $next = 1;
        }

        return 'ORD-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
