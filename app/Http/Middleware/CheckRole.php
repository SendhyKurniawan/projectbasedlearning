<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
// ... (lines 1-15) ...
// ... (lines 17-21) ...
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user role matches the required role provided in the middleware argument
        if (!in_array($user->role, ['admin', 'dosen', 'mahasiswa']) || $user->role !== $requiredRole) {
            // Redirect to appropriate dashboard based on user's *actual* role
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
