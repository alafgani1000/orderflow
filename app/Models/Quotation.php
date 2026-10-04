<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quotation extends Model
{
    use HasFactory;

    const STATUS_DRAFT     = 'draft';
    const STATUS_SENT      = 'sent';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_CONVERTED = 'converted';
    const STATUS_EXPIRED   = 'expired';

    const STATUSES = [
        self::STATUS_DRAFT     => 'Draft',
        self::STATUS_SENT      => 'Terkirim',
        self::STATUS_APPROVED  => 'Disetujui',
        self::STATUS_REJECTED  => 'Ditolak',
        self::STATUS_CONVERTED => 'Dikonversi ke Pesanan',
        self::STATUS_EXPIRED   => 'Kedaluwarsa',
    ];

    protected $fillable = [
        'user_id',
        'customer_id',
        'quotation_number',
        'title',
        'status',
        'valid_until',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'notes',
        'public_token',
        'approved_at',
        'approved_by_name',
        'rejected_at',
        'rejection_reason',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'valid_until'  => 'date',
            'subtotal'     => 'decimal:2',
            'discount'     => 'decimal:2',
            'tax'          => 'decimal:2',
            'total_amount' => 'decimal:2',
            'approved_at'  => 'datetime',
            'rejected_at'  => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation) {
            if (empty($quotation->public_token)) {
                $quotation->public_token = Str::random(48);
            }
        });
    }

    public static function generateQuotationNumber(int $userId): string
    {
        $year = date('Y');
        $prefix = "QUO-{$year}-";

        $lastNumber = static::where('user_id', $userId)
            ->where('quotation_number', 'like', "{$prefix}%")
            ->orderByDesc('quotation_number')
            ->value('quotation_number');

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, strlen($prefix));
            $newSequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '0001';
        }

        return "{$prefix}{$newSequence}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        return $this->valid_until && $this->valid_until->isPast() && ! in_array($this->status, [self::STATUS_APPROVED, self::STATUS_CONVERTED], true);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED || ! is_null($this->order_id);
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canBeConverted(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_APPROVED], true) && is_null($this->order_id);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isExpired() && $this->status !== self::STATUS_CONVERTED) {
            return __('Kedaluwarsa');
        }

        return match ($this->status) {
            self::STATUS_DRAFT     => __('Draft'),
            self::STATUS_SENT      => __('Terkirim'),
            self::STATUS_APPROVED  => __('Disetujui'),
            self::STATUS_REJECTED  => __('Ditolak'),
            self::STATUS_CONVERTED => __('Dikonversi ke Pesanan'),
            self::STATUS_EXPIRED   => __('Kedaluwarsa'),
            default                => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->isExpired() && $this->status !== self::STATUS_CONVERTED) {
            return 'bg-gray-100 text-gray-700 border-gray-300';
        }

        return match ($this->status) {
            self::STATUS_DRAFT     => 'bg-gray-100 text-gray-700 border-gray-300',
            self::STATUS_SENT      => 'bg-sky-100 text-sky-800 border-sky-300',
            self::STATUS_APPROVED  => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::STATUS_REJECTED  => 'bg-rose-100 text-rose-800 border-rose-300',
            self::STATUS_CONVERTED => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            default                => 'bg-gray-100 text-gray-700 border-gray-300',
        };
    }

    public function getPublicUrlAttribute(): string
    {
        return route('quotations.public', $this->public_token);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total_price');
        $discount = (float) $this->discount;
        $tax = (float) $this->tax;
        $total = max(0, ($subtotal - $discount) + $tax);

        $this->update([
            'subtotal'     => $subtotal,
            'total_amount' => $total,
        ]);
    }
}
