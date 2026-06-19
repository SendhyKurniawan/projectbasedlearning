<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

// Komponen layout utama (<x-app-layout>) untuk halaman setelah login.
class AppLayout extends Component
{
    /**
     * Kembalikan view layout aplikasi.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
