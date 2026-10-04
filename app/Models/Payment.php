<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'type',
        'amount',
        'payment_date',
        'method',
        'notes',
        'demo_batch_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    const TYPE_PAYMENT = 'payment';

    const TYPE_REFUND = 'refund';

    const TYPES = [
        'payment' => 'Pembayaran Masuk',
        'refund' => 'Pengembalian Dana (Refund)',
    ];

    const METHODS = [
        'cash' => 'Cash / Tunai',
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'other' => 'Lainnya',
    ];

    public function isRefund(): bool
    {
        return ($this->type ?? self::TYPE_PAYMENT) === self::TYPE_REFUND;
    }

    public function isPayment(): bool
    {
        return ($this->type ?? self::TYPE_PAYMENT) === self::TYPE_PAYMENT;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type ?? self::TYPE_PAYMENT] ?? 'Pembayaran Masuk';
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
