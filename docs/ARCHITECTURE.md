# OrderFlow MVP — Arsitektur & Panduan Developer

Dokumen ini menjelaskan arsitektur internal, struktur direktori, alur data, serta konvensi kode pada aplikasi **OrderFlow MVP**.

---

## 🏛️ 1. Arsitektur Aplikasi

OrderFlow dibangun menggunakan arsitektur **MVC (Model-View-Controller)** standar Laravel 11 dengan tambahan layer **Service** untuk logika bisnis yang dapat digunakan kembali:

```text
[HTTP Request / Browser]
        ↓
    [Routes] (routes/web.php)
        ↓
   [Middleware] (auth, CSRF)
        ↓
   [Controllers] (app/Http/Controllers/*)
   ├── DashboardController
   ├── CustomerController
   ├── OrderController
   ├── PaymentController
   ├── OrderFileController
   └── SettingController
        ↓
    [Services] (app/Services/*)
    ├── OrderNumberService  -> Auto increment ORD-XXXX per user
    └── WhatsAppService     -> Format URL wa.me & template teks
        ↓
     [Models] (app/Models/* Eloquent ORM)
    ├── User
    ├── Customer
    ├── Order
    ├── Payment
    └── OrderFile
        ↓
    [Database] (SQLite / MySQL)
```

---

## 📂 2. Struktur File Utama

```text
orderflow/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                  # Controller autentikasi Breeze
│   │   │   ├── Controller.php         # Base Controller (AuthorizesRequests)
│   │   │   ├── CustomerController.php # CRUD pelanggan & riwayat pesanan
│   │   │   ├── DashboardController.php# Ringkasan KPI & deadline terdekat
│   │   │   ├── OrderController.php    # CRUD pesanan, DP, & status changer
│   │   │   ├── OrderFileController.php# Upload/download berkas desain
│   │   │   ├── PaymentController.php  # Pencatatan cicilan & pelunasan
│   │   │   └── SettingController.php  # Pengaturan profil toko & password
│   │   └── Requests/
│   ├── Models/
│   │   ├── Customer.php               # Model pelanggan (aksesor no. WA 628)
│   │   ├── Order.php                  # Model pesanan (scopes, status flow, kalkulasi)
│   │   ├── OrderFile.php              # Model berkas desain (format size & ikon)
│   │   ├── Payment.php                # Model pembayaran (metode bayar)
│   │   └── User.php                   # Model user pemilik usaha
│   ├── Policies/
│   │   ├── CustomerPolicy.php         # Otorisasi kepemilikan data pelanggan
│   │   └── OrderPolicy.php            # Otorisasi kepemilikan data pesanan
│   └── Services/
│       ├── OrderNumberService.php     # Generator nomor order unik ORD-XXXX
│       └── WhatsAppService.php        # Generator link & template pesan WhatsApp
├── database/
│   ├── migrations/                    # 5 migrasi tabel OrderFlow
│   └── seeders/
│       └── DatabaseSeeder.php         # Data demo siap pakai
├── resources/
│   ├── css/
│   │   └── app.css                    # Tailwind CSS v3 directives
│   ├── js/
│   │   └── app.js                     # Alpine.js & script bootstrap
│   └── views/
│       ├── auth/                      # Login, Register, Forgot Password
│       ├── components/                # Komponen: stat-card, status-badge, empty-state
│       ├── customers/                 # index, create, edit, show
│       ├── orders/                    # index, create, edit, show
│       ├── payments/                  # index (rekap pembayaran)
│       ├── settings/                  # index (identitas usaha & keamanan)
│       ├── layouts/                   # app.blade.php & navigation.blade.php
│       ├── dashboard.blade.php        # Halaman dashboard utama
│       └── welcome.blade.php          # Landing page publik
├── routes/
│   ├── auth.php                       # Rute autentikasi Breeze
│   └── web.php                        # Rute aplikasi OrderFlow
└── tests/
    └── Feature/
        └── OrderFlowTest.php          # Automated test suite lengkap
```

---

## 🔢 3. Logika Penomoran Pesanan (OrderNumberService)

Nomor order digenerate otomatis dengan format `ORD-0001`, `ORD-0002`, dst., yang bersifat unik per pengguna:

```php
// app/Services/OrderNumberService.php
public function generate(int $userId): string
{
    $last = Order::where('user_id', $userId)
        ->orderByDesc('id')
        ->value('order_number');

    $next = $last ? ((int) substr($last, 4) + 1) : 1;
    return 'ORD-' . str_pad($next, 4, '0', STR_PAD_LEFT);
}
```

---

## 🔄 4. Status Pesanan & Transisi Pipeline

Enum status pesanan didefinisikan pada `App\Models\Order`:

```php
const STATUSES = [
    'new'             => 'Baru',
    'waiting_design'  => 'Menunggu Desain',
    'design_approved' => 'Desain Disetujui',
    'production'      => 'Produksi',
    'completed'       => 'Selesai',
    'delivered'       => 'Dikirim / Diambil',
    'cancelled'       => 'Dibatalkan',
];

const STATUS_FLOW = [
    'new'             => 'waiting_design',
    'waiting_design'  => 'design_approved',
    'design_approved' => 'production',
    'production'      => 'completed',
    'completed'       => 'delivered',
];
```

Akses cepat melalui model:
- `$order->status_label` → Mengembalikan teks bahasa Indonesia (misal: "Produksi")
- `$order->status_color` → Mengembalikan warna Tailwind yang sesuai
- `$order->next_status` → Mengembalikan key status berikutnya
- `$order->next_status_label` → Mengembalikan label status berikutnya untuk tombol aksi 1-klik

---

## 💰 5. Kalkulasi Finansial (Total, DP, dan Sisa Tagihan)

Kalkulasi pembayaran ditangani secara dinamis melalui Eloquent Accessors di model `Order`:

```php
// Total yang telah dibayarkan
public function getTotalPaidAttribute(): float
{
    return (float) $this->payments()->sum('amount');
}

// Sisa tagihan yang belum dibayar
public function getRemainingAmountAttribute(): float
{
    return max(0, (float) $this->total_amount - $this->total_paid);
}

// Status apakah sudah lunas
public function getIsPaidOffAttribute(): bool
{
    return $this->remaining_amount <= 0;
}
```

Query scope untuk efisiensi dashboard:
- `Order::active()` → Tidak termasuk pesanan yang sudah *delivered* atau *cancelled*.
- `Order::dueToday()` → Deadline hari ini.
- `Order::overdue()` → Melewati tanggal deadline dan belum selesai.
- `Order::unpaid()` → Pesanan yang total pembayarannya kurang dari total biaya.

---

## 📱 6. Generator WhatsApp (WhatsAppService)

WhatsAppService mengubah nomor lokal (misal: `08123456789`) menjadi format internasional (`628123456789`), lalu mengenkode teks pesan ke format URL standard `https://wa.me/{phone}?text={encoded_message}`.

Template yang tersedia:
1. `statusUrl(Order $order)`
2. `paymentUrl(Order $order, float $amount)`
3. `completedUrl(Order $order)`

---

## 🛡️ 7. Otorisasi Data (Multi-Tenant Scope)

Setiap resource (`Customer`, `Order`, `Payment`, `OrderFile`) terlindungi oleh `Policy` dan query scoping berdasarkan `user_id = auth()->id()`. Pengguna tidak dapat melihat, mengedit, atau menghapus data milik pengguna lain.

---

## 🧪 8. Menjalankan Unit & Feature Test

```bash
# Jalankan seluruh test suite
php artisan test

# Jalankan hanya test OrderFlow
php artisan test --filter=OrderFlowTest
```
