<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! ($request->user()->isAdmin() || $request->user()->isFinance())) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Halaman ini hanya untuk admin/finance.');
        }

        return $next($request);
    }
}
