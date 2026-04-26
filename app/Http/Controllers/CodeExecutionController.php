<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CodeExecutionController extends Controller
{
    public function execute(Request $request)
    {
        $request->validate([
            'code'     => 'required|string',
            'language' => 'required|string|in:' . implode(',', config('code_execution.server_side_languages')),
        ]);

        $pistonLang = match ($request->language) {
            'java'   => ['language' => 'java',   'version' => '*'],
            'php'    => ['language' => 'php',    'version' => '*'],
            'csharp' => ['language' => 'csharp', 'version' => '*'],
            default  => ['language' => $request->language, 'version' => '*'],
        };

        $pistonUrl = config('services.piston.url', 'https://emkc.org/api/v2/piston');

        try {
            $response = Http::timeout(15)->post("{$pistonUrl}/execute", [
                'language' => $pistonLang['language'],
                'version'  => $pistonLang['version'],
                'files'    => [['content' => $request->code]],
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Execution service unavailable.'], 502);
            }

            $body = $response->json();
            $run  = $body['run'] ?? [];

            return response()->json([
                'stdout'    => $run['stdout'] ?? '',
                'stderr'    => $run['stderr'] ?? '',
                'exit_code' => $run['code']   ?? -1,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Execution service error: ' . $e->getMessage()], 502);
        }
    }
}
