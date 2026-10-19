<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Jika belum login atau role tidak sesuai dengan yang diizinkan
        if (!auth()->check() || !in_array(auth()->user()->peran, $roles)) {
            
            // Lempar kasir kembali ke halaman kasir
            if (auth()->check() && auth()->user()->peran === 'kasir') {
                return redirect()->route('kasir.index')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
            
            // Lempar admin ke laporan
            return redirect()->route('laporan.index')->with('error', 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}