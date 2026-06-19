<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

// Komponen layout tamu (<x-guest-layout>) untuk halaman publik/autentikasi (login, register, dll).
class GuestLayout extends Component
{
    /**
     * Kembalikan view layout tamu.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
