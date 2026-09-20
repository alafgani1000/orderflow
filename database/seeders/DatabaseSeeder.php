<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed SaaS Plans
        $starterPlan = Plan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name'                 => 'Starter',
                'description'          => 'Cocok untuk usaha sablon / cetak rumahan yang baru merintis.',
                'price'                => 0,
                'billing_period'       => 'monthly',
                'max_orders_per_month' => 30,
                'max_employees'        => 1,
                'features'             => [
                    'Hingga 30 pesanan / bulan',
                    '1 Akun Pemilik (Owner)',
                    'Papan Produksi Kanban',
                    'WhatsApp Template 1-Klik',
                    'Lacak Pesanan Publik',
                ],
                'is_popular'           => false,
                'is_active'            => true,
            ]
        );

        $proPlan = Plan::firstOrCreate(
            ['slug' => 'pro'],
            [
                'name'                 => 'Pro Juragan',
                'description'          => 'Solusi komplit untuk workshop aktif dengan staf kasir dan operator sablon.',
                'price'                => 49000,
                'billing_period'       => 'monthly',
                'max_orders_per_month' => null, // unlimited
                'max_employees'        => 5,
                'features'             => [
                    'Unlimited Pesanan (Tanpa Batas)',
                    'Hingga 5 Akun Staf (Kasir & Operator)',
                    'Laporan Omset & Keuangan Lengkap',
                    'Export Data Rekap ke Excel / CSV',
                    'Cetak Invoice & Surat Jalan',
                    'Support Prioritas WhatsApp',
                ],
                'is_popular'           => true,
                'is_active'            => true,
            ]
        );

        $enterprisePlan = Plan::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name'                 => 'Enterprise Sultan',
                'description'          => 'Untuk pabrik konveksi & percetakan besar dengan banyak divisi karyawan.',
                'price'                => 99000,
                'billing_period'       => 'monthly',
                'max_orders_per_month' => null, // unlimited
                'max_employees'        => null, // unlimited
                'features'             => [
                    'Unlimited Pesanan',
                    'Unlimited Akun Staf (Tanpa Batas)',
                    'Semua Fitur Pro Juragan',
                    'Dedicated Account Manager',
                    'Bantuan Migrasi Data Khusus',
                ],
                'is_popular'           => false,
                'is_active'            => true,
            ]
        );

        // 2. Seed Super Admin Platform User
        User::firstOrCreate(
            ['email' => 'superadmin@orderflow.test'],
            [
                'name'          => 'Platform SuperAdmin',
                'business_name' => 'OrderFlow SaaS Platform',
                'phone'         => '081199998888',
                'role'          => User::ROLE_SUPERADMIN,
                'password'      => Hash::make('password'),
            ]
        );

        // 3. Demo Tenant User (Owner)
        $user = User::firstOrCreate(
            ['email' => 'admin@orderflow.test'],
            [
                'name'          => 'Budi Owner',
                'business_name' => 'Sablon & Konveksi Juara',
                'phone'         => '081234567890',
                'role'          => User::ROLE_OWNER,
                'password'      => Hash::make('password'),
            ]
        );

        // Assign Pro subscription to demo tenant
        Subscription::firstOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id'        => $proPlan->id,
                'status'         => Subscription::STATUS_ACTIVE,
                'starts_at'      => now(),
                'ends_at'        => now()->addYear(),
                'payment_method' => 'Transfer BCA',
                'notes'          => 'Langganan demo tahunan aktif',
            ]
        );

        // 4. Customers
        $customer1 = Customer::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Budi Santoso'],
            [
                'phone'   => '081234567891',
                'address' => 'Jl. Merdeka No. 45, Bandung',
                'notes'   => 'Pelanggan tetap sablon kaos komunitas',
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Siti Aminah'],
            [
                'phone'   => '081398765432',
                'address' => 'Komplek Permata Indah Blok C2, Jakarta',
                'notes'   => 'Order undangan pernikahan & souvenir',
            ]
        );

        $customer3 = Customer::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Andi Wijaya'],
            [
                'phone'   => '085712345678',
                'address' => 'Jl. Kaliurang KM 7, Yogyakarta',
                'notes'   => 'Order banner spanduk berkala',
            ]
        );

        $customer4 = Customer::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Doni Pratama'],
            [
                'phone'   => '082198765432',
                'address' => 'Ruko Segitiga Emas Blok A5, Surabaya',
                'notes'   => 'Vendor merchandise kantor',
            ]
        );

        // 5. Orders & Payments
        // Order 1: Kaos Komunitas (Jatuh Tempo Hari Ini, DP 500k)
        $order1 = Order::firstOrCreate(
            ['user_id' => $user->id, 'order_number' => 'ORD-0001'],
            [
                'customer_id'    => $customer1->id,
                'name'           => '50 Pcs Kaos Sablon DTF Komunitas Motor',
                'description'    => 'Bahan Cotton Combed 30s warna Hitam Reaktif. Sablon DTF full color dada kiri & punggung A3. Size: M (20), L (20), XL (10).',
                'quantity'       => 50,
                'price_per_unit' => 25000,
                'total_amount'   => 1250000,
                'deadline'       => now()->toDateString(), // Hari ini
                'status'         => 'production',
                'notes'          => 'Target selesai sore ini untuk diambil malam.',
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $order1->id, 'notes' => 'DP / Uang Muka'],
            [
                'amount'       => 500000,
                'payment_date' => now()->subDays(2)->toDateString(),
                'method'       => 'transfer',
            ]
        );

        // Order 2: Undangan Pernikahan (Menunggu Desain, DP 300k)
        $order2 = Order::firstOrCreate(
            ['user_id' => $user->id, 'order_number' => 'ORD-0002'],
            [
                'customer_id'    => $customer2->id,
                'name'           => '300 Pcs Undangan Hardcover Gold Foil',
                'description'    => 'Kertas Jasmine 230gr dengan hotprint gold nama pengantin. Termasuk plastik dan kartu ucapan terima kasih.',
                'quantity'       => 300,
                'price_per_unit' => 3000,
                'total_amount'   => 900000,
                'deadline'       => now()->addDays(2)->toDateString(),
                'status'         => 'waiting_design',
                'notes'          => 'Menunggu revisi denah lokasi dari customer.',
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $order2->id, 'notes' => 'DP Awal'],
            [
                'amount'       => 300000,
                'payment_date' => now()->subDays(1)->toDateString(),
                'method'       => 'qris',
            ]
        );

        // Order 3: Banner Terlambat (Overdue, Belum DP)
        $order3 = Order::firstOrCreate(
            ['user_id' => $user->id, 'order_number' => 'ORD-0003'],
            [
                'customer_id'    => $customer3->id,
                'name'           => '2 Pcs Spanduk Flexi Korea 440gr (3x1 meter)',
                'description'    => 'Finishing mata ayam setiap 1 meter keliling. Desain siap cetak.',
                'quantity'       => 2,
                'price_per_unit' => 75000,
                'total_amount'   => 150000,
                'deadline'       => now()->subDays(1)->toDateString(), // Lewat 1 hari
                'status'         => 'production',
                'notes'          => 'Segera diselesaikan karena sudah melewati deadline.',
            ]
        );

        // Order 4: Totebag Kanvas Selesai Lunas
        $order4 = Order::firstOrCreate(
            ['user_id' => $user->id, 'order_number' => 'ORD-0004'],
            [
                'customer_id'    => $customer4->id,
                'name'           => '100 Pcs Totebag Kanvas Sablon Rubber',
                'description'    => 'Bahan Kanvas Marsoto Broken White ukuran 30x40cm resleting.',
                'quantity'       => 100,
                'price_per_unit' => 15000,
                'total_amount'   => 1500000,
                'deadline'       => now()->subDays(3)->toDateString(),
                'status'         => 'completed',
                'notes'          => 'Sudah di-packing rapi, menunggu konfirmasi pengiriman.',
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $order4->id, 'notes' => 'DP 50%'],
            [
                'amount'       => 750000,
                'payment_date' => now()->subDays(5)->toDateString(),
                'method'       => 'transfer',
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $order4->id, 'notes' => 'Pelunasan'],
            [
                'amount'       => 750000,
                'payment_date' => now()->subDays(1)->toDateString(),
                'method'       => 'transfer',
            ]
        );
    }
}
