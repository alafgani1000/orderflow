<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_OWNER      = 'owner';
    const ROLE_ADMIN_CS   = 'admin_cs';
    const ROLE_PRODUCTION = 'production';

    const ROLES = [
        self::ROLE_SUPERADMIN => 'Super Admin Platform',
        self::ROLE_OWNER      => 'Owner / Pemilik Toko',
        self::ROLE_ADMIN_CS   => 'Kasir / Admin CS',
        self::ROLE_PRODUCTION => 'Operator Workshop / Desain',
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'business_name',
        'phone',
        'role',
        'owner_id',
        'google_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'owner_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'user_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'user_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class, 'user_id');
    }

    /**
     * Dapatkan ID Owner yang memiliki data toko (jika staff, mengembalikan owner_id).
     */
    public function getStoreOwnerId(): int
    {
        return $this->owner_id ?? $this->id;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isOwner(): bool
    {
        return ($this->role ?? self::ROLE_OWNER) === self::ROLE_OWNER;
    }

    public function isAdminCs(): bool
    {
        return $this->role === self::ROLE_ADMIN_CS;
    }

    public function isProduction(): bool
    {
        return $this->role === self::ROLE_PRODUCTION;
    }

    /**
     * Apakah user diizinkan melihat nominal uang dan omset?
     */
    public function canViewFinances(): bool
    {
        return in_array($this->role ?? self::ROLE_OWNER, [
            self::ROLE_SUPERADMIN,
            self::ROLE_OWNER,
            self::ROLE_ADMIN_CS,
        ]);
    }

    /**
     * Dapatkan objek langganan aktif toko (bila staff, merujuk ke langganan owner).
     */
    public function currentSubscription(): ?Subscription
    {
        if ($this->owner_id) {
            return $this->owner?->subscription()->with('plan')->first();
        }

        return $this->subscription()->with('plan')->first();
    }

    /**
     * Cek apakah toko memiliki langganan atau masa uji coba aktif.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->owner_id) {
            return $this->owner ? $this->owner->hasActiveSubscription() : true;
        }

        $subscription = $this->currentSubscription();
        if (!$subscription) {
            // Default true jika belum ada record langganan (kompatibilitas test lama)
            return true;
        }

        return $subscription->isActive();
    }

    /**
     * Jumlah pesanan yang dibuat oleh toko di bulan kalender ini.
     */
    public function currentMonthOrdersCount(): int
    {
        $storeOwnerId = $this->getStoreOwnerId();
        return Order::where('user_id', $storeOwnerId)
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();
    }

    /**
     * Cek apakah toko masih diperbolehkan membuat order baru berdasarkan kuota paket.
     */
    public function canCreateOrder(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $subscription = $this->currentSubscription();
        if (!$subscription || !$subscription->plan) {
            return true;
        }

        $plan = $subscription->plan;
        if ($plan->hasUnlimitedOrders()) {
            return true;
        }

        return $this->currentMonthOrdersCount() < $plan->max_orders_per_month;
    }

    /**
     * Jumlah staf yang dimiliki oleh toko saat ini.
     */
    public function currentEmployeesCount(): int
    {
        $storeOwnerId = $this->getStoreOwnerId();
        return User::where('owner_id', $storeOwnerId)->count();
    }

    /**
     * Cek apakah toko masih diperbolehkan menambah staf berdasarkan kuota paket.
     */
    public function canAddEmployee(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->isOwner()) {
            return false;
        }

        $subscription = $this->currentSubscription();
        if (!$subscription || !$subscription->plan) {
            return true;
        }

        $plan = $subscription->plan;
        if ($plan->hasUnlimitedEmployees()) {
            return true;
        }

        return $this->currentEmployeesCount() < $plan->max_employees;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? 'Owner / Pemilik Toko';
    }

    public function getShortRoleLabelAttribute(): string
    {
        return match($this->role) {
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_ADMIN_CS   => 'Kasir / CS',
            self::ROLE_PRODUCTION => 'Operator',
            default               => 'Owner',
        };
    }
}
