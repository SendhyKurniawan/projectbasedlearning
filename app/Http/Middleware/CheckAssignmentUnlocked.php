<?php

namespace App\Http\Middleware;

use App\Models\Assignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAssignmentUnlocked
{
    /**
     * Handle an incoming request to ensure assignment is unlocked for the student.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get assignment from route parameter
        $assignment = $request->route('assignment');
        
        // If assignment exists and is an Assignment model instance
        if ($assignment instanceof Assignment) {
            // Check if assignment is unlocked for current user
            if (!$assignment->isUnlockedFor(auth()->id())) {
                return redirect()
                    ->back()
                    ->with('error', 'Anda harus membaca materi prerequisite terlebih dahulu untuk mengakses tugas ini.');
            }
        }
        
        return $next($request);
    }
}
