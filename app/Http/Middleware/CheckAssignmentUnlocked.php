<?php

namespace App\Http\Middleware;

use App\Models\Assignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAssignmentUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $assignment = $request->route('assignment');

        if ($assignment instanceof Assignment) {
            if (!$assignment->isUnlockedFor(auth()->id())) {
                return redirect()
                    ->back()
                    ->with('error', 'Anda harus membaca materi prerequisite terlebih dahulu untuk mengakses tugas ini.');
            }
        }

        return $next($request);
    }
}
