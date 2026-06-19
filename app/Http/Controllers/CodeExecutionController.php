<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Controller proxy eksekusi kode: meneruskan kode dari klien ke Piston API di sisi server
// (jangan dipanggil langsung dari frontend). Di-throttle 10/menit oleh route.
class CodeExecutionController extends Controller
{
    // Jalankan kode lewat Piston. Bahasa dibatasi allowlist & dipetakan ke nama bahasa Piston.
    public function execute(Request $request): JsonResponse
    {
        $map = config('code_execution.piston_language_map', []);

        $validated = $request->validate([
            'code' => 'required|string|max:50000',
            'language' => 'required|string|in:' . implode(',', array_keys($map)),
        ]);

        $pistonLanguage = $map[$validated['language']];
        $base = rtrim((string) config('services.piston.url'), '/');
        $timeout = (int) config('services.piston.timeout', 10);

        try {
            $response = Http::timeout($timeout)->acceptJson()->post($base . '/execute', [
                'language' => $pistonLanguage,
                'version'  => '*',
                'files'    => [['content' => $validated['code']]],
            ]);
        } catch (ConnectionException | RequestException $e) {
            // Gagal menghubungi Piston → kembalikan respons "layanan tidak tersedia".
            Log::warning('Piston request failed', ['error' => $e->getMessage()]);
            return $this->serviceUnavailable();
        }

        if (!$response->successful()) {
            Log::warning('Piston returned non-2xx', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return $this->serviceUnavailable();
        }

        $run = $response->json('run', []);

        return response()->json([
            'stdout'    => (string) ($run['stdout'] ?? ''),
            'stderr'    => (string) ($run['stderr'] ?? ''),
            'exit_code' => (int) ($run['code'] ?? -1),
        ]);
    }

    // Respons standar saat layanan eksekusi (Piston) tidak dapat diakses (HTTP 502).
    private function serviceUnavailable(): JsonResponse
    {
        return response()->json([
            'stdout'    => '',
            'stderr'    => 'Execution service unavailable.',
            'exit_code' => -1,
        ], 502);
    }
}
