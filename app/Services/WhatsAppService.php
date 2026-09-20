<?php

namespace App\Services;

use App\Models\Order;

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
        $customer     = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;

        $deadline = $order->deadline
            ? $order->deadline->translatedFormat('d F Y')
            : '-';

        $trackingInfo = $order->tracking_url 
            ? "🔍 Lacak progres pengerjaan:\n" . $order->tracking_url . "\n\n" 
            : "";

        return "Halo {$customer->name} 👋\n\n"
            . "Pesanan #{$order->order_number} (*{$order->name}*) saat ini:\n"
            . "📦 Status: *{$order->status_label}*\n"
            . "📅 Deadline: {$deadline}\n\n"
            . "Sisa pembayaran: *Rp" . number_format($order->remaining_amount, 0, ',', '.') . "*\n\n"
            . $trackingInfo
            . "Terima kasih 🙏\n"
            . "- {$businessName}";
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
        $customer     = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;

        $trackingInfo = $order->tracking_url 
            ? "🔍 Cek rincian pesanan & nota:\n" . $order->tracking_url . "\n\n" 
            : "";

        return "Halo {$customer->name} 👋\n\n"
            . "Kami konfirmasi pembayaran untuk pesanan #{$order->order_number}:\n"
            . "💰 Dibayar: *Rp" . number_format($amount, 0, ',', '.') . "*\n"
            . "✅ Total lunas: *Rp" . number_format($order->total_paid, 0, ',', '.') . "*\n"
            . "📋 Sisa: *Rp" . number_format($order->remaining_amount, 0, ',', '.') . "*\n\n"
            . $trackingInfo
            . "Terima kasih atas pembayarannya 🙏\n"
            . "- {$businessName}";
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
        $customer     = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;

        $deadline = $order->deadline
            ? $order->deadline->translatedFormat('d F Y')
            : '-';

        $trackingInfo = $order->tracking_url 
            ? "🔍 Cek rincian nota & progres online:\n" . $order->tracking_url . "\n\n" 
            : "";

        return "Halo kak {$customer->name} 👋\n\n"
            . "Mengingatkan untuk sisa tagihan pesanan #{$order->order_number} (*{$order->name}*):\n"
            . "📦 Status: *{$order->status_label}*\n"
            . "💰 Sisa Pembayaran: *Rp" . number_format($order->remaining_amount, 0, ',', '.') . "*\n"
            . "📅 Target Selesai: {$deadline}\n\n"
            . $trackingInfo
            . "Silakan lakukan pelunasan agar pesanan dapat segera dikirim / diambil ya kak. Terima kasih banyak 🙏\n"
            . "- {$businessName}";
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
        $customer     = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;
        $methodLabel  = \App\Models\Payment::METHODS[$method] ?? $method;

        $trackingInfo = $order->tracking_url 
            ? "🔍 Rincian transaksi pesanan:\n" . $order->tracking_url . "\n\n" 
            : "";

        return "Halo kak {$customer->name} 👋\n\n"
            . "Kami konfirmasikan bahwa *pengembalian dana (refund)* untuk pesanan #{$order->order_number} (*{$order->name}*) telah kami serahkan/proses:\n"
            . "💸 Nominal Refund: *Rp" . number_format($amount, 0, ',', '.') . "*\n"
            . "💳 Metode: *{$methodLabel}*\n"
            . "📅 Tanggal: " . now()->translatedFormat('d F Y') . "\n\n"
            . $trackingInfo
            . "Mohon cek mutasi/fisik uang tunai yang telah kami serahkan. Terima kasih atas pengertiannya 🙏\n"
            . "- {$businessName}";
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
        $customer     = $order->customer;
        $businessName = auth()->user()->business_name ?? auth()->user()->name;

        $trackingInfo = $order->tracking_url 
            ? "🔍 Detail pesanan & nota:\n" . $order->tracking_url . "\n\n" 
            : "";

        $message = "Halo {$customer->name} 👋\n\n"
            . "Pesanan Anda sudah *SELESAI* 🎉\n\n"
            . "📦 Pesanan: *{$order->name}*\n"
            . "🔢 Order: #{$order->order_number}\n"
            . "📦 Qty: {$order->quantity} pcs\n\n";

        if ($order->remaining_amount > 0) {
            $message .= "💳 Sisa pembayaran: *Rp" . number_format($order->remaining_amount, 0, ',', '.') . "*\n\n";
        }

        $message .= "Silakan hubungi kami untuk pengiriman/pengambilan.\n\n"
            . $trackingInfo
            . "Terima kasih sudah mempercayai kami 🙏\n"
            . "- {$businessName}";

        return $message;
    }

    /**
     * Generate wa.me URL untuk notifikasi pesanan selesai.
     */
    public function completedUrl(Order $order): string
    {
        return $this->buildUrl($order->customer->whats_app_number, $this->completedMessage($order));
    }

    private function buildUrl(string $phone, string $message): string
    {
        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }
}
