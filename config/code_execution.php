<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bahasa server-side
    |--------------------------------------------------------------------------
    |
    | Bahasa yang tombol "Run"-nya diteruskan ke Piston via /execute-code.
    | Bahasa di luar daftar ini dijalankan di sisi klien dalam iframe preview.
    | Harus cocok dengan nilai opsi pada form dosen.exercises.{create,edit}.
    |
    */
    'server_side_languages' => ['java', 'php', 'csharp'],

    /*
    |--------------------------------------------------------------------------
    | Bahasa aplikasi → identifier bahasa Piston
    |--------------------------------------------------------------------------
    |
    | Frontend mengirim slug versi aplikasi kita; CodeExecutionController
    | menerjemahkannya ke nama yang dikenali Piston. emkc.org/api/v2/piston
    | memakai `csharp.net` (Mono) untuk C#; bahasa lain dipetakan 1:1.
    |
    */
    'piston_language_map' => [
        'java'   => 'java',
        'php'    => 'php',
        'csharp' => 'csharp.net',
    ],

];
