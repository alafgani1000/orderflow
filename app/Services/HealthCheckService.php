<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthCheckService
{
    /**
     * Jalankan seluruh pemeriksaan kesehatan sistem dan kembalikan laporan terstruktur.
     */
    public function check(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage'  => $this->checkStorage(),
            'cache'    => $this->checkCache(),
            'backup'   => $this->checkBackup(),
        ];

        $allOk = collect($checks)->every(fn ($c) => $c['status'] === 'ok' || $c['status'] === 'warning');
        $hasCritical = collect($checks)->contains(fn ($c) => $c['status'] === 'error');

        return [
            'status'     => $hasCritical ? 'error' : ($allOk ? 'healthy' : 'warning'),
            'timestamp'  => now()->toIso8601String(),
            'app_env'    => config('app.env'),
            'checks'     => $checks,
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $count = DB::table('users')->count();

            return [
                'status'  => 'ok',
                'message' => "Basis data terhubung ({$count} pengguna).",
            ];
        } catch (\Throwable $e) {
            return [
                'status'  => 'error',
                'message' => 'Gagal terhubung ke basis data: ' . $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $privateDir = storage_path('app/private');
            if (! File::isDirectory($privateDir)) {
                File::makeDirectory($privateDir, 0755, true);
            }

            $testFile = $privateDir . DIRECTORY_SEPARATOR . '.health_write_test';
            File::put($testFile, 'test');
            File::delete($testFile);

            return [
                'status'  => 'ok',
                'message' => 'Storage privat dapat ditulisi dan dibaca.',
            ];
        } catch (\Throwable $e) {
            return [
                'status'  => 'error',
                'message' => 'Storage privat gagal ditulisi: ' . $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_ping_' . microtime(true);
            Cache::put($key, 'pong', 10);
            $val = Cache::get($key);
            Cache::forget($key);

            if ($val !== 'pong') {
                return ['status' => 'warning', 'message' => 'Cache driver tidak merespon nilai yang sesuai.'];
            }

            return ['status' => 'ok', 'message' => 'Cache aktif dan responsif.'];
        } catch (\Throwable $e) {
            return ['status' => 'warning', 'message' => 'Peringatan cache: ' . $e->getMessage()];
        }
    }

    private function checkBackup(): array
    {
        $backupDir = config('orderflow.backup.path', storage_path('app/backups'));
        if (! File::isDirectory($backupDir)) {
            return [
                'status'  => 'warning',
                'message' => 'Direktori cadangan belum memiliki arsip.',
            ];
        }

        $files = File::glob($backupDir . DIRECTORY_SEPARATOR . 'orderflow_backup_*.zip');
        if (empty($files)) {
            return [
                'status'  => 'warning',
                'message' => 'Belum ada berkas cadangan ditemukan. Jalankan php artisan orderflow:backup.',
            ];
        }

        // Cari file cadangan terbaru
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        $latest = $files[0];
        $latestTime = filemtime($latest);
        $ageHours = round((time() - $latestTime) / 3600, 1);
        $maxAge = (int) config('orderflow.backup.max_age_hours', 26);

        if ($ageHours > $maxAge) {
            return [
                'status'  => 'warning',
                'message' => "Cadangan terakhir dibuat {$ageHours} jam yang lalu (batas aman: {$maxAge} jam).",
                'latest'  => basename($latest),
            ];
        }

        return [
            'status'  => 'ok',
            'message' => "Cadangan terbaru tersedia ({$ageHours} jam lalu: " . basename($latest) . ').',
            'latest'  => basename($latest),
        ];
    }
}
