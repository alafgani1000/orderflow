<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->canViewFinances()) {
            abort(403, 'Anda tidak memiliki akses ke laporan keuangan.');
        }

        $storeOwnerId = $user->getStoreOwnerId();
        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($request);

        // Orders in period
        $ordersQuery = Order::where('user_id', $storeOwnerId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalOmset      = (clone $ordersQuery)->sum('total_amount');
        $totalOrdersCount = (clone $ordersQuery)->count();
        $completedCount  = (clone $ordersQuery)->whereIn('status', ['completed', 'delivered'])->count();

        // Payments received in period
        $paymentsQuery = Payment::whereHas('order', fn($q) => $q->where('user_id', $storeOwnerId))
            ->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $totalCashReceived = (clone $paymentsQuery)->sum('amount');

        // Unpaid receivables for orders created in this period
        $totalUnpaidReceivables = (clone $ordersQuery)->where('status', '!=', 'cancelled')->get()->sum(fn($o) => $o->remaining_amount);

        // Payment method breakdown
        $paymentMethods = [
            'cash'     => (clone $paymentsQuery)->where('method', 'cash')->sum('amount'),
            'transfer' => (clone $paymentsQuery)->where('method', 'transfer')->sum('amount'),
            'qris'     => (clone $paymentsQuery)->where('method', 'qris')->sum('amount'),
            'other'    => (clone $paymentsQuery)->where('method', 'other')->sum('amount'),
        ];

        // Orders list
        $orders = (clone $ordersQuery)
            ->with(['customer', 'payments'])
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('reports.index', compact(
            'periodLabel',
            'startDate',
            'endDate',
            'totalOmset',
            'totalCashReceived',
            'totalUnpaidReceivables',
            'totalOrdersCount',
            'completedCount',
            'paymentMethods',
            'orders'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = auth()->user();
        if (!$user->canViewFinances()) {
            abort(403, 'Anda tidak memiliki akses export laporan.');
        }

        $storeOwnerId = $user->getStoreOwnerId();
        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($request);

        $orders = Order::where('user_id', $storeOwnerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['customer', 'payments'])
            ->orderBy('created_at')
            ->get();

        $businessName = $user->business_name ?: 'OrderFlow';
        $filename = "Laporan-{$businessName}-" . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'No',
                'No. Order',
                'Tanggal Pesan',
                'Target Selesai',
                'Nama Pelanggan',
                'WhatsApp Pelanggan',
                'Nama Pesanan',
                'Jumlah (Pcs)',
                'Rincian Ukuran',
                'Harga Satuan (Rp)',
                'Total Tagihan (Rp)',
                'Total Terbayar (Rp)',
                'Sisa Tagihan (Rp)',
                'Status Pengerjaan',
                'Status Pembayaran',
            ]);

            $sanitize = function ($val) {
                if (is_string($val) && strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@', "\t", "\r"])) {
                    return "'" . $val;
                }
                return $val;
            };

            $no = 1;
            foreach ($orders as $order) {
                fputcsv($handle, array_map($sanitize, [
                    $no++,
                    $order->order_number,
                    $order->created_at->format('d/m/Y H:i'),
                    $order->deadline ? $order->deadline->format('d/m/Y') : '-',
                    $order->customer->name,
                    $order->customer->phone ?: '-',
                    $order->name,
                    $order->quantity,
                    $order->size_summary ?: '-',
                    $order->price_per_unit,
                    $order->total_amount,
                    $order->total_paid,
                    $order->remaining_amount,
                    $order->status_label,
                    $order->is_paid_off ? 'Lunas' : 'Belum Lunas',
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function resolveDateRange(Request $request): array
    {
        $period = $request->input('period', 'this_month');

        switch ($period) {
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end   = Carbon::now()->subMonth()->endOfMonth();
                $label = 'Bulan Lalu (' . $start->translatedFormat('F Y') . ')';
                break;

            case 'this_year':
                $start = Carbon::now()->startOfYear();
                $end   = Carbon::now()->endOfYear();
                $label = 'Tahun Ini (' . $start->format('Y') . ')';
                break;

            case 'custom':
                $start = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
                $end   = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
                $label = $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
                break;

            case 'this_month':
            default:
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfMonth();
                $label = 'Bulan Ini (' . $start->translatedFormat('F Y') . ')';
                break;
        }

        return [$start, $end, $label];
    }
}
