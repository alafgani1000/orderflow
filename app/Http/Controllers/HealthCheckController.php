<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthCheckController extends Controller
{
    public function __invoke(Request $request, HealthCheckService $service): JsonResponse
    {
        $configuredToken = config('orderflow.health.token');
        $providedToken = $request->header('X-Health-Token') ?? $request->query('token');

        // Jika token dikonfigurasi dan token tidak cocok, sembunyikan detail internal
        if (filled($configuredToken) && $providedToken !== $configuredToken) {
            return response()->json([
                'status' => 'ok',
                'message' => 'OrderFlow is alive. Gunakan token otorisasi untuk laporan kesehatan lengkap.',
            ], 200);
        }

        $report = $service->check();
        $httpCode = $report['status'] === 'error' ? 503 : 200;

        return response()->json($report, $httpCode);
    }
}
