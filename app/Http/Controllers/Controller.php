<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

// Kelas dasar untuk semua controller; menyediakan kemampuan otorisasi (policy) & validasi.
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
