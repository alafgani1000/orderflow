<?php

namespace App\Console\Commands;

use App\Services\HealthCheckService;
use Illuminate\Console\Command;

class HealthCheckCommand extends Command
{
    protected $signature = 'orderflow:health';

    protected $description = 'Periksa kesehatan seluruh subsistem OrderFlow (database, storage, backup, cache).';

    public function handle(HealthCheckService $service): int
    {
        $this->info('Memeriksa kesehatan subsistem OrderFlow...');

        $report = $service->check();

        $rows = [];
        foreach ($report['checks'] as $key => $check) {
            $badge = match ($check['status']) {
                'ok'      => '<info>OK</info>',
                'warning' => '<comment>WARNING</comment>',
                default   => '<error>ERROR</error>',
            };
            $rows[] = [ucfirst($key), $badge, $check['message']];
        }

        $this->table(['Subsistem', 'Status', 'Keterangan'], $rows);

        if ($report['status'] === 'error') {
            $this->error('Terdapat komponen dengan status ERROR.');
            return self::FAILURE;
        }

        $this->info('Semua subsistem dalam kondisi baik.');
        return self::SUCCESS;
    }
}
