<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CatatMetrik
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // /metrics dan /health tidak dihitung, supaya angka lalu lintas hanya berisi permintaan pengguna
        if (! $request->is('metrics', 'health')) {
            try {
                $route = $request->route()?->uri() ?? 'tidak_dikenal';
                $key = implode('|', [$request->method(), $route, $response->getStatusCode()]);

                Cache::lock('metrik-http-lock', 5)->block(3, function () use ($key) {
                    $data = Cache::get('metrik-http', []);
                    $data[$key] = ($data[$key] ?? 0) + 1;
                    Cache::forever('metrik-http', $data);
                });
            } catch (Throwable $e) {
                // Pencatatan metrik tidak boleh membuat request pengguna gagal
                report($e);
            }
        }

        return $response;
    }
}