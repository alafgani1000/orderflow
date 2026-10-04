<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'order_number',
        'name',
        'description',
        'quantity',
        'price_per_unit',
        'total_amount',
        'deadline',
        'status',
        'notes',
        'tracking_token',
        'size_breakdown',
        'demo_batch_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->tracking_token)) {
                $order->tracking_token = Str::random(24);
            }
        });
    }

    protected $casts = [
        'deadline' => 'date',
        'price_per_unit' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'size_breakdown' => 'array',
    ];

    // ─── Status labels ────────────────────────────────────────────────────────

    const STATUSES = [
        'new' => 'Baru',
        'waiting_design' => 'Menunggu Desain',
        'design_approved' => 'Desain Disetujui',
        'production' => 'Produksi',
        'completed' => 'Selesai',
        'delivered' => 'Dikirim / Diambil',
        'cancelled' => 'Dibatalkan',
    ];

    const STATUS_COLORS = [
        'new' => 'gray',
        'waiting_design' => 'yellow',
        'design_approved' => 'blue',
        'production' => 'orange',
        'completed' => 'green',
        'delivered' => 'teal',
        'cancelled' => 'red',
    ];

    // Urutan transisi status berikutnya
    const STATUS_FLOW = [
        'new' => 'waiting_design',
        'waiting_design' => 'design_approved',
        'design_approved' => 'production',
        'production' => 'completed',
        'completed' => 'delivered',
    ];

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getNextStatusAttribute(): ?string
    {
        return self::STATUS_FLOW[$this->status] ?? null;
    }

    public function getNextStatusLabelAttribute(): ?string
    {
        $next = $this->next_status;

        return $next ? self::STATUSES[$next] : null;
    }

    public function getTotalPaidAttribute(): float
    {
        $payments = (float) $this->payments()->where('type', 'payment')->sum('amount');
        $refunds = (float) $this->payments()->where('type', 'refund')->sum('amount');

        return max(0, $payments - $refunds);
    }

    public function getTotalRefundedAttribute(): float
    {
        return (float) $this->payments()->where('type', 'refund')->sum('amount');
    }

    public function getHasRefundAttribute(): bool
    {
        return $this->total_refunded > 0;
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->total_paid);
    }

    public function getIsPaidOffAttribute(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->is_paid_off;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline
            && $this->deadline->isPast()
            && ! in_array($this->status, ['completed', 'delivered', 'cancelled']);
    }

    public function getIsDueTodayAttribute(): bool
    {
        return $this->deadline
            && $this->deadline->isToday()
            && ! in_array($this->status, ['completed', 'delivered', 'cancelled']);
    }

    public function getTrackingUrlAttribute(): string
    {
        return $this->tracking_token ? route('orders.track', $this->tracking_token) : '';
    }

    public function getHasSizeBreakdownAttribute(): bool
    {
        return ! empty($this->size_breakdown) && is_array($this->size_breakdown);
    }

    public function getSizeSummaryAttribute(): ?string
    {
        if (! $this->has_size_breakdown) {
            return null;
        }

        $parts = [];
        foreach ($this->size_breakdown as $size => $qty) {
            if ($qty > 0) {
                $parts[] = "{$size}: {$qty}";
            }
        }

        return ! empty($parts) ? implode(', ', $parts) : null;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->whereNotIn('status', ['completed', 'delivered', 'cancelled']);
    }

    public function scopeDueToday($query)
    {
        return $query->where('deadline', now()->toDateString())
            ->whereNotIn('status', ['completed', 'delivered', 'cancelled']);
    }

    public function scopeUnpaid($query)
    {
        // Orders where total_paid < total_amount (accounting for refunds)
        return $query->whereNotIn('status', ['cancelled'])
            ->whereRaw('(SELECT COALESCE(SUM(CASE WHEN type = "refund" THEN -amount ELSE amount END), 0) FROM payments WHERE payments.order_id = orders.id) < orders.total_amount');
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date');
    }

    public function files(): HasMany
    {
        return $this->hasMany(OrderFile::class);
    }

    public function quotation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Quotation::class);
    }
}
