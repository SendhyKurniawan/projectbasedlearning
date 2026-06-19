<?php

namespace App\Http\Middleware;

use App\Models\Assignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Middleware penjaga prasyarat tugas: blokir akses tugas bila materi prasyaratnya
// belum dibaca mahasiswa (lihat Assignment::isUnlockedFor).
class CheckAssignmentUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $assignment = $request->route('assignment');

        if ($assignment instanceof Assignment) {
            // Belum terbuka → kembalikan dengan pesan agar membaca materi prasyarat dulu.
            if (!$assignment->isUnlockedFor(auth()->id())) {
                return redirect()
                    ->back()
                    ->with('error', 'Anda harus membaca materi prerequisite terlebih dahulu untuk mengakses tugas ini.');
            }
        }

        return $next($request);
    }
}
