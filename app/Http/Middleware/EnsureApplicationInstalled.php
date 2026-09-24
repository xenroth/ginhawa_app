<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('install*') || $request->is('up')) {
            return $next($request);
        }

        try {
            if (!Schema::hasTable('roles')) {
                Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            }
        } catch (\Throwable $exception) {
            report($exception);
            return response()->view('install', ['error' => $exception->getMessage()], 503);
        }

        return $next($request);
    }
}