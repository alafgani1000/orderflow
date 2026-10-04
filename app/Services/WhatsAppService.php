<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class WhatsAppService
{
    /**
     * Generate wa.me URL dengan pesan template status pesanan.
     */
    /**
     * Teks pesan status pesanan.
     */
    public function statusMessage(Order $order): string
    {
        $customer = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;

        $deadline = $order->deadline
            ? $order->deadline->translatedFormat('d F Y')
            : '-';

        $trackingInfo = $order->tracking_url
            ? __('🔍 Lacak progres pengerjaan:')."\n".$order->tracking_url."\n\n"
            : '';

        return __('Halo :name 👋', ['name' => $customer->name])."\n\n"
            .__('Pesanan #:number (*:order*) saat ini:', ['number' => $order->order_number, 'order' => $order->name])."\n"
            .__('📦 Status: *:status*', ['status' => __($order->status_label)])."\n"
            .__('📅 Deadline: :date', ['date' => $deadline])."\n\n"
            .__('Sisa pembayaran: *Rp:amount*', ['amount' => number_format($order->remaining_amount, 0, ',', '.')])."\n\n"
            .$trackingInfo
            .__('Terima kasih 🙏')."\n"
            ."- {$businessName}";
    }

    /**
     * Generate wa.me URL dengan pesan template status pesanan.
     */
    public function statusUrl(Order $order): string
    {
        return $this->buildUrl($order->customer->whats_app_number, $this->statusMessage($order));
    }

    /**
     * Teks pesan konfirmasi pembayaran.
     */
    public function paymentMessage(Order $order, float $amount): string
    {
        $customer = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;

        $trackingInfo = $order->tracking_url
            ? __('🔍 Cek rincian pesanan & nota:')."\n".$order->tracking_url."\n\n"
            : '';

        return __('Halo :name 👋', ['name' => $customer->name])."\n\n"
            .__('Kami konfirmasi pembayaran untuk pesanan #:number:', ['number' => $order->order_number])."\n"
            .__('💰 Dibayar: *Rp:amount*', ['amount' => number_format($amount, 0, ',', '.')])."\n"
            .__('✅ Total lunas: *Rp:amount*', ['amount' => number_format($order->total_paid, 0, ',', '.')])."\n"
            .__('📋 Sisa: *Rp:amount*', ['amount' => number_format($order->remaining_amount, 0, ',', '.')])."\n\n"
            .$trackingInfo
            .__('Terima kasih atas pembayarannya 🙏')."\n"
            ."- {$businessName}";
    }

    /**
     * Generate wa.me URL untuk konfirmasi pembayaran.
     */
    public function paymentUrl(Order $order, float $amount): string
    {
        return $this->buildUrl($order->customer->whats_app_number, $this->paymentMessage($order, $amount));
    }

    /**
     * Teks pesan pengingat sisa tagihan (Payment Reminder).
     */
    public function reminderMessage(Order $order): string
    {
        $customer = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;

        $deadline = $order->deadline
            ? $order->deadline->translatedFormat('d F Y')
            : '-';

        $trackingInfo = $order->tracking_url
            ? __('🔍 Cek rincian nota & progres online:')."\n".$order->tracking_url."\n\n"
            : '';

        return __('Halo kak :name 👋', ['name' => $customer->name])."\n\n"
            .__('Mengingatkan untuk sisa tagihan pesanan #:number (*:order*):', ['number' => $order->order_number, 'order' => $order->name])."\n"
            .__('📦 Status: *:status*', ['status' => __($order->status_label)])."\n"
            .__('💰 Sisa Pembayaran: *Rp:amount*', ['amount' => number_format($order->remaining_amount, 0, ',', '.')])."\n"
            .__('📅 Target Selesai: :date', ['date' => $deadline])."\n\n"
            .$trackingInfo
            .__('Silakan lakukan pelunasan agar pesanan dapat segera dikirim / diambil. Terima kasih banyak 🙏')."\n"
            ."- {$businessName}";
    }

    /**
     * Generate wa.me URL untuk pengingat sisa tagihan.
     */
    public function reminderUrl(Order $order): string
    {
        return $this->buildUrl($order->customer->whats_app_number, $this->reminderMessage($order));
    }

    /**
     * Teks pesan konfirmasi pengembalian dana (Refund).
     */
    public function refundMessage(Order $order, float $amount, string $method = 'cash'): string
    {
        $customer = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;
        $methodLabel = __(Payment::METHODS[$method] ?? $method);

        $trackingInfo = $order->tracking_url
            ? __('🔍 Rincian transaksi pesanan:')."\n".$order->tracking_url."\n\n"
            : '';

        return __('Halo kak :name 👋', ['name' => $customer->name])."\n\n"
            .__('Kami konfirmasikan bahwa *pengembalian dana (refund)* untuk pesanan #:number (*:order*) telah kami proses:', ['number' => $order->order_number, 'order' => $order->name])."\n"
            .__('💸 Nominal Refund: *Rp:amount*', ['amount' => number_format($amount, 0, ',', '.')])."\n"
            .__('💳 Metode: *:method*', ['method' => $methodLabel])."\n"
            .__('📅 Tanggal: :date', ['date' => now()->translatedFormat('d F Y')])."\n\n"
            .$trackingInfo
            .__('Mohon periksa mutasi rekening atau uang tunai yang telah kami serahkan. Terima kasih atas pengertiannya 🙏')."\n"
            ."- {$businessName}";
    }

    /**
     * Generate wa.me URL untuk bukti pengembalian dana.
     */
    public function refundUrl(Order $order, float $amount, string $method = 'cash'): string
    {
        return $this->buildUrl($order->customer->whats_app_number, $this->refundMessage($order, $amount, $method));
    }

    /**
     * Teks pesan notifikasi pesanan selesai.
     */
    public function completedMessage(Order $order): string
    {
        $customer = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;

        $trackingInfo = $order->tracking_url
            ? __('🔍 Detail pesanan & nota:')."\n".$order->tracking_url."\n\n"
            : '';

        $message = __('Halo :name 👋', ['name' => $customer->name])."\n\n"
            .__('Pesanan Anda sudah *SELESAI* 🎉')."\n\n"
            .__('📦 Pesanan: *:order*', ['order' => $order->name])."\n"
            .__('🔢 Order: #:number', ['number' => $order->order_number])."\n"
            .__('📦 Jumlah: :quantity pcs', ['quantity' => $order->quantity])."\n\n";

        if ($order->remaining_amount > 0) {
            $message .= __('💳 Sisa pembayaran: *Rp:amount*', ['amount' => number_format($order->remaining_amount, 0, ',', '.')])."\n\n";
        }

        $message .= __('Silakan hubungi kami untuk pengiriman/pengambilan.')."\n\n"
            .$trackingInfo
            .__('Terima kasih sudah mempercayai kami 🙏')."\n"
            ."- {$businessName}";

        return $message;
    }

    /**
     * Generate wa.me URL untuk notifikasi pesanan selesai.
     */
    public function completedUrl(Order $order): string
    {
        return $this->buildUrl($order->customer->whats_app_number, $this->completedMessage($order));
    }

    /**
     * Teks pesan penawaran harga untuk pelanggan.
     */
    public function quotationMessage(\App\Models\Quotation $quotation): string
    {
        $customer = $quotation->customer;
        $user = auth()->user();
        $businessName = $user ? ($user->business_name ?? $user->name) : ($quotation->user->business_name ?? $quotation->user->name);

        $validUntil = $quotation->valid_until
            ? $quotation->valid_until->translatedFormat('d F Y')
            : '-';

        return __('Halo :name 👋', ['name' => $customer->name])."\n\n"
            .__('Berikut penawaran harga resmi dari *:business*:', ['business' => $businessName])."\n"
            .__('📋 Penawaran: *:title*', ['title' => $quotation->title])."\n"
            .__('🔢 No: *:number*', ['number' => $quotation->quotation_number])."\n"
            .__('💰 Total: *Rp:amount*', ['amount' => number_format($quotation->total_amount, 0, ',', '.')])."\n"
            .__('📅 Berlaku Hingga: :date', ['date' => $validUntil])."\n\n"
            .__('🔍 Lihat rincian & konfirmasi penawaran di tautan berikut:')."\n"
            .$quotation->public_url."\n\n"
            .__('Terima kasih 🙏')."\n"
            ."- {$businessName}";
    }

    public function quotationUrl(\App\Models\Quotation $quotation): string
    {
        return $this->buildUrl($quotation->customer->whats_app_number, $this->quotationMessage($quotation));
    }

    private function buildUrl(string $phone, string $message): string
    {
        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    }
}
