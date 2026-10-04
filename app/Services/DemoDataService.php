<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataService
{
    public function __construct(private OrderNumberService $orderNumberService) {}

    /** @return array{created: bool, customers: int, orders: int, payments: int} */
    public function create(User $owner): array
    {
        $hasDemoData = Customer::where('user_id', $owner->id)->whereNotNull('demo_batch_id')->exists()
            || Order::where('user_id', $owner->id)->whereNotNull('demo_batch_id')->exists();

        if ($hasDemoData) {
            return ['created' => false, 'customers' => 0, 'orders' => 0, 'payments' => 0];
        }

        return DB::transaction(function () use ($owner): array {
            $batchId = (string) Str::uuid();
            $english = $owner->locale === 'en';
            $customerTemplates = $english ? $this->englishCustomers() : $this->indonesianCustomers();
            $orderTemplates = $english ? $this->englishOrders() : $this->indonesianOrders();
            $customers = collect();

            foreach ($customerTemplates as $template) {
                $customers->push(Customer::create([
                    ...$template,
                    'user_id' => $owner->id,
                    'demo_batch_id' => $batchId,
                ]));
            }

            $paymentCount = 0;
            foreach ($orderTemplates as $template) {
                $payments = $template['payments'];
                unset($template['payments']);
                $customerIndex = $template['customer_index'];
                unset($template['customer_index']);

                $order = Order::create([
                    ...$template,
                    'user_id' => $owner->id,
                    'customer_id' => $customers[$customerIndex]->id,
                    'order_number' => $this->orderNumberService->generate($owner->id),
                    'demo_batch_id' => $batchId,
                ]);

                foreach ($payments as $payment) {
                    Payment::create([
                        ...$payment,
                        'order_id' => $order->id,
                        'demo_batch_id' => $batchId,
                    ]);
                    $paymentCount++;
                }
            }

            return [
                'created' => true,
                'customers' => count($customerTemplates),
                'orders' => count($orderTemplates),
                'payments' => $paymentCount,
            ];
        });
    }

    /** @return array{customers: int, orders: int, payments: int, preserved_customers: int} */
    public function delete(User $owner): array
    {
        return DB::transaction(function () use ($owner): array {
            $demoOrders = Order::where('user_id', $owner->id)->whereNotNull('demo_batch_id');
            $demoOrderIds = (clone $demoOrders)->pluck('id');
            $paymentCount = Payment::whereIn('order_id', $demoOrderIds)->count();
            $orderCount = $demoOrderIds->count();

            Payment::whereIn('order_id', $demoOrderIds)->delete();
            $demoOrders->delete();

            $demoCustomers = Customer::where('user_id', $owner->id)->whereNotNull('demo_batch_id')->get();
            $deletedCustomers = 0;
            $preservedCustomers = 0;

            foreach ($demoCustomers as $customer) {
                if ($customer->orders()->exists()) {
                    $customer->update(['demo_batch_id' => null]);
                    $preservedCustomers++;
                } else {
                    $customer->delete();
                    $deletedCustomers++;
                }
            }

            return [
                'customers' => $deletedCustomers,
                'orders' => $orderCount,
                'payments' => $paymentCount,
                'preserved_customers' => $preservedCustomers,
            ];
        });
    }

    /** @return array<int, array<string, string>> */
    private function indonesianCustomers(): array
    {
        return [
            ['name' => 'Komunitas Riders Bandung', 'phone' => '081200000101', 'address' => 'Bandung, Jawa Barat', 'notes' => 'Data contoh — pelanggan komunitas'],
            ['name' => 'CV Kreatif Nusantara', 'phone' => '081200000102', 'address' => 'Jakarta Selatan', 'notes' => 'Data contoh — pelanggan perusahaan'],
            ['name' => 'Rina Merchandise', 'phone' => '081200000103', 'address' => 'Bekasi, Jawa Barat', 'notes' => 'Data contoh — pelanggan reseller'],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function englishCustomers(): array
    {
        return [
            ['name' => 'Bandung Riders Community', 'phone' => '081200000101', 'address' => 'Bandung, West Java', 'notes' => 'Sample data — community customer'],
            ['name' => 'Creative Nusantara Ltd.', 'phone' => '081200000102', 'address' => 'South Jakarta', 'notes' => 'Sample data — business customer'],
            ['name' => 'Rina Merchandise', 'phone' => '081200000103', 'address' => 'Bekasi, West Java', 'notes' => 'Sample data — reseller customer'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function indonesianOrders(): array
    {
        return [
            $this->orderTemplate(0, 'Kaos Gathering Komunitas', 'Cotton Combed 30s hitam, sablon depan dan belakang.', 50, 75000, 3750000, now()->addDays(7), 'new'),
            $this->orderTemplate(1, 'Seragam Event Perusahaan', 'Polo shirt navy dengan bordir logo.', 30, 95000, 2850000, now()->addDays(4), 'waiting_design', [500000]),
            $this->orderTemplate(2, 'Tote Bag Merchandise', 'Canvas natural, cetak satu sisi.', 100, 28000, 2800000, now(), 'production', [1000000]),
            $this->orderTemplate(0, 'Jaket Panitia Touring', 'Jaket parasut dengan bordir nama.', 25, 165000, 4125000, now()->subDays(2), 'production', [1500000]),
            $this->orderTemplate(1, 'Banner Booth Pameran', 'Flexi 280 gsm ukuran 3 × 2 meter.', 2, 350000, 700000, now()->subDay(), 'completed', [700000]),
            $this->orderTemplate(2, 'Mug Souvenir', 'Mug putih cetak full color.', 24, 35000, 840000, now()->subDays(7), 'delivered', [840000]),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function englishOrders(): array
    {
        return [
            $this->orderTemplate(0, 'Community Gathering T-Shirts', 'Black 30s combed cotton, front and back print.', 50, 75000, 3750000, now()->addDays(7), 'new'),
            $this->orderTemplate(1, 'Corporate Event Uniforms', 'Navy polo shirts with embroidered logo.', 30, 95000, 2850000, now()->addDays(4), 'waiting_design', [500000]),
            $this->orderTemplate(2, 'Merchandise Tote Bags', 'Natural canvas, one-sided print.', 100, 28000, 2800000, now(), 'production', [1000000]),
            $this->orderTemplate(0, 'Tour Committee Jackets', 'Windbreaker jackets with embroidered names.', 25, 165000, 4125000, now()->subDays(2), 'production', [1500000]),
            $this->orderTemplate(1, 'Exhibition Booth Banner', '280 gsm flex banner, 3 × 2 metres.', 2, 350000, 700000, now()->subDay(), 'completed', [700000]),
            $this->orderTemplate(2, 'Souvenir Mugs', 'White mugs with full-colour print.', 24, 35000, 840000, now()->subDays(7), 'delivered', [840000]),
        ];
    }

    /** @param array<int, int> $payments */
    private function orderTemplate(int $customerIndex, string $name, string $description, int $quantity, int $unitPrice, int $total, \DateTimeInterface $deadline, string $status, array $payments = []): array
    {
        return [
            'customer_index' => $customerIndex,
            'name' => $name,
            'description' => $description,
            'quantity' => $quantity,
            'price_per_unit' => $unitPrice,
            'total_amount' => $total,
            'deadline' => $deadline->format('Y-m-d'),
            'status' => $status,
            'notes' => app()->getLocale() === 'en' ? 'Sample data' : 'Data contoh',
            'payments' => array_map(fn (int $amount): array => [
                'type' => Payment::TYPE_PAYMENT,
                'amount' => $amount,
                'payment_date' => now()->subDay()->toDateString(),
                'method' => 'transfer',
                'notes' => app()->getLocale() === 'en' ? 'Sample payment' : 'Pembayaran contoh',
            ], $payments),
        ];
    }
}
