<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    const STATUS_TRIALING = 'trialing';
    const STATUS_ACTIVE   = 'active';
    const STATUS_PAST_DUE = 'past_due';
    const STATUS_EXPIRED  = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'     => 'datetime',
            'ends_at'       => 'datetime',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function isActive(): bool
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return is_null($this->ends_at) || $this->ends_at->isFuture();
        }

        if ($this->status === self::STATUS_TRIALING) {
            return is_null($this->trial_ends_at) || $this->trial_ends_at->isFuture();
        }

        return false;
    }

    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIALING &&
            (is_null($this->trial_ends_at) || $this->trial_ends_at->isFuture());
    }

    public function isExpired(): bool
    {
        return !$this->isActive();
    }

    public function getExpiryDate(): ?Carbon
    {
        if ($this->status === self::STATUS_TRIALING) {
            return $this->trial_ends_at;
        }

        return $this->ends_at;
    }

    public function getDaysRemainingAttribute(): int
    {
        $expiry = $this->getExpiryDate();
        if (!$expiry) {
            return 999;
        }

        if ($expiry->isPast()) {
            return 0;
        }

        return (int) ceil(now()->floatDiffInDays($expiry));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_TRIALING  => 'Uji Coba Gratis (' . $this->days_remaining . ' hari lagi)',
            self::STATUS_ACTIVE    => 'Aktif Berlangganan',
            self::STATUS_PAST_DUE  => 'Menunggu Pembayaran',
            self::STATUS_EXPIRED   => 'Kedaluwarsa',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default                => ucfirst($this->status),
        };
    }
}
