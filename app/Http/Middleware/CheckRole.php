<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Middleware penjaga peran (dipakai sebagai `role:admin|dosen|mahasiswa` pada grup route).
// Memastikan user yang login berperan sesuai; jika tidak, dipantulkan ke dashboard-nya sendiri.
class CheckRole
{
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        // Belum login → arahkan ke halaman login.
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'dosen', 'mahasiswa']) || $user->role !== $requiredRole) {
            // Peran tidak cocok → pantulkan ke dashboard sesuai peran user.
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'dosen' => redirect()->route('dosen.dashboard'),
                'mahasiswa' => redirect()->route('mahasiswa.dashboard'),
                default => redirect()->route('login'),
            };
        }

        return $next($request);
    }
}
