<?php

namespace App\Console\Commands;

use App\Models\OrderFile;
use App\Models\SubscriptionInvoice;
use App\Services\SecureFileStorage;
use Illuminate\Console\Command;

class SecureFilesCommand extends Command
{
    protected $signature = 'orderflow:secure-files {--dry-run : Tampilkan berkas yang akan dipindahkan tanpa mengubahnya}';

    protected $description = 'Pindahkan berkas desain dan bukti pembayaran lama dari storage publik ke storage privat.';

    public function handle(SecureFileStorage $storage): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'Memeriksa berkas yang perlu diamankan (DRY RUN)...' : 'Memindahkan berkas ke storage privat...');

        $migratedOrders = 0;
        $migratedInvoices = 0;

        // 1. Periksa berkas desain pesanan
        $orderFiles = OrderFile::whereNotNull('file_path')->get();
        foreach ($orderFiles as $file) {
            if ($storage->legacyDisk()->exists($file->file_path)) {
                if ($dryRun) {
                    $this->line("  [DRY] Akan memindahkan berkas desain: {$file->file_path}");
                    $migratedOrders++;
                } else {
                    if ($storage->migrateFromLegacy($file->file_path)) {
                        $this->line("  ✓ Berkas desain dipindahkan ke privat: {$file->file_path}");
                        $migratedOrders++;
                    }
                }
            }
        }

        // 2. Periksa bukti pembayaran langganan
        $invoices = SubscriptionInvoice::whereNotNull('payment_proof')->get();
        foreach ($invoices as $inv) {
            if ($storage->legacyDisk()->exists($inv->payment_proof)) {
                if ($dryRun) {
                    $this->line("  [DRY] Akan memindahkan bukti invoice: {$inv->payment_proof}");
                    $migratedInvoices++;
                } else {
                    if ($storage->migrateFromLegacy($inv->payment_proof)) {
                        $this->line("  ✓ Bukti invoice dipindahkan ke privat: {$inv->payment_proof}");
                        $migratedInvoices++;
                    }
                }
            }
        }

        $total = $migratedOrders + $migratedInvoices;
        if ($total === 0) {
            $this->info('Semua berkas sudah berada di storage privat atau tidak ada berkas publik lama.');
        } else {
            $this->info($dryRun
                ? "Ditemukan {$total} berkas yang perlu dipindahkan ({$migratedOrders} desain, {$migratedInvoices} bukti)."
                : "Selesai mengamankan {$total} berkas ({$migratedOrders} desain, {$migratedInvoices} bukti) ke storage privat.");
        }

        return self::SUCCESS;
    }
}
