<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_period',
        'max_orders_per_month',
        'max_employees',
        'features',
        'is_popular',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'                => 'decimal:2',
            'max_orders_per_month' => 'integer',
            'max_employees'        => 'integer',
            'features'             => 'array',
            'is_popular'           => 'boolean',
            'is_active'            => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function isFree(): bool
    {
        return (float) $this->price <= 0;
    }

    public function hasUnlimitedOrders(): bool
    {
        return is_null($this->max_orders_per_month);
    }

    public function hasUnlimitedEmployees(): bool
    {
        return is_null($this->max_employees);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFree()) {
            return 'Gratis';
        }

        return 'Rp' . number_format($this->price, 0, ',', '.');
    }
}
