<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class MetricsController extends Controller
{
    public function __invoke(): Response
    {
        $data = Cache::get('metrik-http', []);

        $lines = [
            '# HELP http_requests_total Jumlah permintaan HTTP yang diterima aplikasi',
            '# TYPE http_requests_total counter',
        ];

        foreach ($data as $key => $total) {
            [$method, $route, $status] = explode('|', $key);

            $lines[] = sprintf(
                'http_requests_total{method="%s",route="%s",status="%s"} %d',
                $method,
                $route,
                $status,
                $total
            );
        }

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }
}