<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'orderflow:backup {--include-files : Sertakan private storage files ke dalam arsip backup}';

    protected $description = 'Buat cadangan database dan berkas privat OrderFlow serta bersihkan cadangan lama.';

    public function handle(): int
    {
        $this->info('Memulai proses pencadangan OrderFlow...');

        $backupDir = config('orderflow.backup.path', storage_path('app/backups'));
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Ymd_His');
        $zipPath = $backupDir . DIRECTORY_SEPARATOR . "orderflow_backup_{$timestamp}.zip";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Gagal membuat arsip ZIP di: {$zipPath}");
            return self::FAILURE;
        }

        // 1. Cadangkan Database
        $defaultConnection = config('database.default');
        if ($defaultConnection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if (File::exists($dbPath)) {
                $zip->addFile($dbPath, 'database/database.sqlite');
                $this->line("  ✓ Database SQLite berhasil dimasukkan.");
            } else {
                $this->warn("  ! Berkas SQLite tidak ditemukan di: {$dbPath}");
            }
        } else {
            // Untuk MySQL / PostgreSQL, kita catat metadata koneksi
            $zip->addFromString('database/connection_info.json', json_encode([
                'connection' => $defaultConnection,
                'timestamp' => now()->toIso8601String(),
                'note' => 'Gunakan mysqldump / pg_dump untuk basis data eksternal.',
            ], JSON_PRETTY_PRINT));
        }

        // 2. Cadangkan Berkas Privat jika diminta
        $includeFiles = $this->option('include-files') || config('orderflow.backup.include_files', true);
        if ($includeFiles) {
            $privateStoragePath = storage_path('app/private');
            if (File::isDirectory($privateStoragePath)) {
                $files = File::allFiles($privateStoragePath);
                $fileCount = 0;
                foreach ($files as $file) {
                    $relative = 'files/' . str_replace('\\', '/', $file->getRelativePathname());
                    $zip->addFile($file->getRealPath(), $relative);
                    $fileCount++;
                }
                $this->line("  ✓ {$fileCount} berkas privat berhasil dimasukkan.");
            }
        }

        // 3. Tambahkan Manifest
        $zip->addFromString('manifest.json', json_encode([
            'app' => config('app.name'),
            'env' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0-paid-beta',
            'included_files' => $includeFiles,
        ], JSON_PRETTY_PRINT));

        $zip->close();
        $this->info("✓ Cadangan berhasil dibuat: {$zipPath} (" . round(filesize($zipPath) / 1024, 2) . " KB)");

        // 4. Rotasi Pembersihan Cadangan Lama
        $retentionDays = (int) config('orderflow.backup.retention_days', 14);
        $this->pruneOldBackups($backupDir, $retentionDays);

        return self::SUCCESS;
    }

    private function pruneOldBackups(string $backupDir, int $retentionDays): void
    {
        $cutoff = Carbon::now()->subDays($retentionDays)->getTimestamp();
        $files = File::glob($backupDir . DIRECTORY_SEPARATOR . 'orderflow_backup_*.zip');

        $pruned = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                File::delete($file);
                $pruned++;
            }
        }

        if ($pruned > 0) {
            $this->line("  ✓ Menghapus {$pruned} cadangan usang yang lebih lama dari {$retentionDays} hari.");
        }
    }
}
