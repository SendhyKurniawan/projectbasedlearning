<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server-side languages
    |--------------------------------------------------------------------------
    |
    | Languages whose "Run" button proxies to Piston via /execute-code.
    | Anything outside this list runs client-side in the preview iframe.
    | Must match the option values used in dosen.exercises.{create,edit} forms.
    |
    */
    'server_side_languages' => ['java', 'php', 'csharp'],

    /*
    |--------------------------------------------------------------------------
    | App language → Piston language identifier
    |--------------------------------------------------------------------------
    |
    | The frontend posts our app-side slug; CodeExecutionController translates
    | to whatever Piston actually expects. emkc.org/api/v2/piston exposes
    | `csharp.net` (Mono) for C#; the others map 1:1.
    |
    */
    'piston_language_map' => [
        'java'   => 'java',
        'php'    => 'php',
        'csharp' => 'csharp.net',
    ],

];
