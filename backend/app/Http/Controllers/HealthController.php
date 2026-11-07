<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
        ];

        $healthy = collect($checks)->every(fn ($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'time'   => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::select('select 1');

            return ['status' => 'ok', 'latency_ms' => round((microtime(true) - $start) * 1000, 1)];
        } catch (Throwable $e) {
            Log::error('Health check gagal: basis data tidak dapat dihubungi', [
                'aksi'     => 'health.check',
                'komponen' => 'database',
                'alasan'   => 'koneksi_database_gagal',
                'penyebab' => Str::limit($e->getMessage(), 300),
            ]);

            return ['status' => 'error', 'message' => 'Basis data tidak dapat dihubungi'];
        }
    }

    private function checkCache(): array
    {
        $start = microtime(true);

        try {
            $key = 'health-check-' . Str::random(8);
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            if ($value !== 'ok') {
                throw new \RuntimeException('Nilai cache yang dibaca tidak sama dengan yang ditulis');
            }

            return ['status' => 'ok', 'latency_ms' => round((microtime(true) - $start) * 1000, 1)];
        } catch (Throwable $e) {
            Log::error('Health check gagal: cache tidak berfungsi', [
                'aksi'     => 'health.check',
                'komponen' => 'cache',
                'alasan'   => 'cache_gagal',
                'penyebab' => Str::limit($e->getMessage(), 300),
            ]);

            return ['status' => 'error', 'message' => 'Cache tidak berfungsi'];
        }
    }
}