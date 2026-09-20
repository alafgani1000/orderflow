# OrderFlow — Manajemen Pesanan Sablon, Percetakan & Konveksi (MVP)

> **Solusi sederhana untuk pemilik usaha custom agar tidak kehilangan pesanan, deadline, dan pembayaran dari WhatsApp.**

---

## 📌 1. Tentang OrderFlow

OrderFlow adalah aplikasi berbasis web yang dirancang khusus untuk membantu pemilik usaha custom (sablon, percetakan, konveksi kecil, undangan, banner, merchandise, dan souvenir) mengelola pesanan yang masuk melalui WhatsApp dalam **satu tempat**.

### Target Pengguna:
- Usaha sablon kaos & merchandise
- Percetakan & digital printing
- Konveksi seragam & pakaian
- Percetakan undangan & souvenir
- Usaha banner, stiker, dan custom order lainnya

---

## 🎯 2. Masalah yang Diselesaikan

| Masalah Usaha Custom | Solusi OrderFlow |
|---|---|
| ❌ Pesanan tercecer di riwayat chat WhatsApp | ✅ Satu daftar terpusat dengan nomor order unik (`ORD-XXXX`) |
| ❌ Deadline pengerjaan sering terlewat | ✅ Dashboard memantau pesanan **Jatuh Tempo Hari Ini** dan **Terlambat** |
| ❌ Status produksi tidak jelas bagi tim dan customer | ✅ Status pipeline 6 tahap & tombol ubah status 1-klik |
| ❌ Uang muka (DP) dan sisa pelunasan sulit dilacak | ✅ Pencatatan DP + cicilan dengan kalkulasi sisa pembayaran otomatis |
| ❌ Capek mengetik ulang kabar status ke WhatsApp customer | ✅ Tombol **wa.me 1-klik** dengan pesan template otomatis |

---

## ✨ 3. Fitur Utama MVP

```text
┌─────────────────────────────────────────────────────────────┐
│                       DASHBOARD RINGKASAN                   │
├───────────────┬───────────────────┬─────────────┬───────────┤
│ Pesanan Aktif │ Jatuh Tempo Hari  │  Terlambat  │Belum Lunas│
│      27       │        3          │      2      │     8     │
└───────────────┴───────────────────┴─────────────┴───────────┘
```

### 1. 📊 Dashboard Ringkasan
- **4 Kartu KPI**: Pesanan Aktif, Jatuh Tempo Hari Ini, Terlambat (*Overdue*), Belum Lunas.
- **Tabel Pesanan Mendesak**: 10 pesanan aktif terdekat dengan deadline pengerjaan.

### 2. 👥 Manajemen Pelanggan (*Customer*)
- Tambah, edit, cari, dan hapus pelanggan.
- Nomor WhatsApp terformat otomatis ke standar internasional (`628xxx`).
- Profil pelanggan dilengkapi riwayat seluruh pesanan yang pernah dibuat.
- Tombol **Chat WhatsApp** langsung di setiap pelanggan.

### 3. 📦 Manajemen Pesanan (*Orders*)
- Auto-generate nomor order unik: `ORD-0001`, `ORD-0002`, dst.
- Kalkulasi otomatis total biaya: `Jumlah (pcs) × Harga Satuan`.
- Pencatatan DP (uang muka) langsung saat pesanan dibuat.
- Filter pesanan berdasarkan:
  - **Status**: Baru, Menunggu Desain, Desain Disetujui, Produksi, Selesai, Dikirim, Dibatalkan.
  - **Deadline**: Hari Ini, Terlambat, 7 Hari Kedepan.
  - **Pencarian**: Nomor order, nama pelanggan, nama pesanan.

### 4. 🔄 Alur Status Pengerjaan (*Pipeline*)
Visual tracker alur pengerjaan workshop yang ringkas:
```text
Baru → Menunggu Desain → Desain Disetujui → Produksi → Selesai → Dikirim / Diambil
```
- Tombol 1-klik untuk maju ke tahap berikutnya.
- Opsi pembatalan (*Cancelled*).

### 5. 💰 Pencatatan Pembayaran & DP
- Pencatatan pembayaran uang muka dan pelunasan bertahap.
- Pilihan metode bayar: **Cash / Tunai**, **Transfer Bank**, **QRIS**, **Lainnya**.
- Pelacakan sisa tagihan secara *real-time*.
- Rekap seluruh transaksi pembayaran masuk di halaman `/payments`.

### 6. 📱 Integrasi WhatsApp (wa.me)
Fitur 1-klik membuka WhatsApp dengan pesan yang sudah diformat rapi (tanpa biaya API):
- **Template Update Status & Deadline**: Mengabarkan status pengerjaan, deadline, dan sisa pembayaran.
- **Template Konfirmasi Pembayaran**: Tanda terima pembayaran DP/pelunasan.
- **Template Pesanan Selesai**: Mengabarkan bahwa pesanan siap diambil/dikirim.

### 7. 📁 Berkas Desain & Mockup
- Upload multiple file desain sekaligus (`.jpg`, `.png`, `.pdf`, `.zip`, `.rar`, `.ai`, `.psd`).
- Download file dan hapus berkas terintegrasi dengan storage lokal.

### 8. ⚙️ Pengaturan Identitas Usaha
- Konfigurasi nama pemilik, nama brand/toko/konveksi, nomor WhatsApp usaha.
- Keamanan: Ubah kata sandi / password akun.

---

## 🛠️ 4. Tech Stack

- **Backend**: [Laravel 11](https://laravel.com) (PHP 8.2+)
- **Database**: SQLite (kompatibel untuk local dev dan siap migrasi ke MariaDB/PostgreSQL/Supabase)
- **Frontend**: Blade Templating + [Tailwind CSS v3](https://tailwindcss.com) + Alpine.js
- **Auth**: Laravel Breeze
- **Storage**: Laravel Storage Local Disk
- **WhatsApp**: Link `https://wa.me/{phone}?text={message}` (Zero-cost, Zero-risk)

---

## 🚀 5. Cara Instalasi & Menjalankan

### Prasyarat:
- PHP >= 8.2 (ekstensi `pdo_sqlite`, `mbstring`, `fileinfo` aktif)
- Composer >= 2.0
- Node.js >= 18 & npm

### Langkah Instalasi:

```bash
# 1. Masuk ke folder project
cd d:/source_code/orderflow

# 2. Install dependency PHP & Node.js (jika baru di-clone)
composer install
npm install

# 3. Buat database SQLite & jalankan migration beserta data demo
php artisan migrate --seed

# 4. Buat symbolic link storage
php artisan storage:link

# 5. Build asset frontend (Tailwind CSS)
npm run build

# 6. Jalankan local development server
php artisan serve
```

Buka browser Anda di: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔑 6. Akun Demo Siap Pakai

Database seeder sudah menyediakan data realistis:

| Field | Kredensial Demo |
|---|---|
| **URL Login** | `http://127.0.0.1:8000/login` |
| **Email** | `admin@orderflow.test` |
| **Password** | `password` |
| **Nama Usaha** | Sablon & Konveksi Juara |
| **No. WhatsApp** | 081234567890 |

---

## 🗄️ 7. Struktur Database & Relasi

```text
User (Pemilik Usaha)
 │
 ├── Customers (Pelanggan)
 │      │
 │      └── Orders (Pesanan)
 │             │
 │             ├── Payments (Pembayaran)
 │             │
 │             └── Order Files (File Desain)
 │
 └── Orders
```

### Skema Tabel:

#### `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Identifier |
| `name` | VARCHAR | Nama pemilik |
| `business_name` | VARCHAR | Nama toko / konveksi |
| `phone` | VARCHAR | No. WhatsApp admin |
| `email` | VARCHAR Unique | Email login |
| `password` | VARCHAR | Hash kata sandi |

#### `customers`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Identifier |
| `user_id` | BIGINT FK | Relasi ke `users` |
| `name` | VARCHAR | Nama pelanggan |
| `phone` | VARCHAR | No. WhatsApp |
| `address` | TEXT | Alamat pengiriman |
| `notes` | TEXT | Catatan preferensi |

#### `orders`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Identifier |
| `user_id` | BIGINT FK | Relasi ke `users` |
| `customer_id` | BIGINT FK | Relasi ke `customers` |
| `order_number` | VARCHAR Unique | `ORD-0001`, `ORD-0002` |
| `name` | VARCHAR | Judul pesanan |
| `description` | TEXT | Spesifikasi teknis |
| `quantity` | INT | Jumlah pcs/unit |
| `price_per_unit` | DECIMAL | Harga per pcs |
| `total_amount` | DECIMAL | Total biaya |
| `deadline` | DATE | Tanggal target selesai |
| `status` | ENUM | `new`, `waiting_design`, `design_approved`, `production`, `completed`, `delivered`, `cancelled` |
| `notes` | TEXT | Catatan khusus |

#### `payments`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Identifier |
| `order_id` | BIGINT FK | Relasi ke `orders` |
| `amount` | DECIMAL | Nominal bayar |
| `payment_date` | DATE | Tanggal transaksi |
| `method` | ENUM | `cash`, `transfer`, `qris`, `other` |
| `notes` | TEXT | Catatan pembayaran |

#### `order_files`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT PK | Identifier |
| `order_id` | BIGINT FK | Relasi ke `orders` |
| `file_name` | VARCHAR | Nama asli file |
| `file_path` | VARCHAR | Path di storage |
| `file_type` | VARCHAR | Mime type |
| `file_size` | BIGINT | Ukuran dalam bytes |

---

## 💬 8. Format Template Pesan WhatsApp

### 1. Template Update Status:
```text
Halo Budi Santoso 👋

Pesanan #ORD-0001 (*50 Pcs Kaos Sablon DTF Komunitas*) saat ini:
📦 Status: *Produksi*
📅 Deadline: 25 Agustus 2026

Sisa pembayaran: *Rp750.000*

Terima kasih 🙏
- Sablon & Konveksi Juara
```

### 2. Template Konfirmasi Pembayaran:
```text
Halo Budi Santoso 👋

Kami konfirmasi pembayaran untuk pesanan #ORD-0001:
💰 Dibayar: *Rp500.000*
✅ Total lunas: *Rp500.000*
📋 Sisa: *Rp750.000*

Terima kasih atas pembayarannya 🙏
- Sablon & Konveksi Juara
```

### 3. Template Pesanan Selesai:
```text
Halo Budi Santoso 👋

Pesanan Anda sudah *SELESAI* 🎉

📦 Pesanan: *50 Pcs Kaos Sablon DTF Komunitas*
🔢 Order: #ORD-0001
📦 Qty: 50 pcs

💳 Sisa pembayaran: *Rp750.000*

Silakan hubungi kami untuk pengiriman/pengambilan.

Terima kasih sudah mempercayai kami 🙏
- Sablon & Konveksi Juara
```

---

## 🧪 9. Pengujian & Verifikasi

Semua fitur telah dilengkapi test otomatis menggunakan PHPUnit:

```bash
php artisan test
```

### Hasil Test:
```text
   PASS  Tests\Unit\ExampleTest
   PASS  Tests\Feature\Auth\AuthenticationTest
   PASS  Tests\Feature\Auth\EmailVerificationTest
   PASS  Tests\Feature\Auth\PasswordConfirmationTest
   PASS  Tests\Feature\Auth\PasswordResetTest
   PASS  Tests\Feature\Auth\PasswordUpdateTest
   PASS  Tests\Feature\Auth\RegistrationTest
   PASS  Tests\Feature\OrderFlowTest
  ✓ landing page can be rendered
  ✓ user can register with business fields
  ✓ dashboard displays kpi counters
  ✓ can create customer
  ✓ can create order with auto generated order number
  ✓ can update order status
  ✓ can record payment and reduce remaining balance
  ✓ whatsapp service generates valid url

  Tests:    27 passed (67 assertions)
  Duration: 100% OK
```

---

## 🗺️ 10. Roadmap Pengembangan Selanjutnya

- [ ] **Fase 2**: Reminder deadline otomatis via WA Gateway (Fonnte/Wablas), Cetak Invoice PDF / Surat Jalan.
- [ ] **Fase 3**: Integrasi WhatsApp Cloud API resmi, Laporan omset bulanan & grafik penjualan.
- [ ] **Fase 4**: Manajemen stok bahan baku (kain, tinta, kertas), Akun multi-staff / operator sablon.

---

## 📄 Lisensi

OrderFlow MVP dikembangkan sebagai solusi perangkat lunak open-source di bawah lisensi [MIT License](LICENSE).
