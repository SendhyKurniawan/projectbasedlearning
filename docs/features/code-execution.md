# Sandbox Eksekusi Kode (Code Execution)

## Apa ini

Proxy sisi-server yang memungkinkan mahasiswa "Run" kodenya dari halaman pengerjaan exercise (dan dosen "Preview" dari editor exercise) tanpa memberi browser akses langsung ke runtime. Proxy meneruskan request ke instance **Piston** — secara default kontainer docker `piston` bawaan — dan mengembalikan `{stdout, stderr, exit_code}`.

- **Controller**: `App\Http\Controllers\CodeExecutionController` (auth-only, dibatasi)
- **Route**: `POST /execute-code` (`execute.code`), middleware `auth + throttle:10,1` (10 request per menit per user)
- **Config**: `config/code_execution.php` (bahasa yang diizinkan) + `config/services.php` (URL/timeout Piston)
- **Runtime bawaan**: kontainer docker `ghcr.io/engineer-man/piston`

```php
// routes/web.php (di dalam grup auth-only)
Route::post('/execute-code', [CodeExecutionController::class, 'execute'])
    ->name('execute.code')
    ->middleware('throttle:10,1');
```

---

## Allowlist bahasa

`config/code_execution.php`:

```php
return [
    'server_side_languages' => ['java', 'php', 'csharp'],

    'piston_language_map' => [
        'java'   => 'java',
        'php'    => 'php',
        'csharp' => 'csharp.net',     // Piston menjalankan Mono untuk C#
    ],
];
```

- **Bahasa server-side** (`java`, `php`, `csharp`) → dirutekan lewat `/execute-code` → Piston.
- **Bahasa client-side** (`html`, `css`, `javascript`, `htmlmixed`) → dirender di `<iframe>` ber-sandbox langsung di browser; tanpa roundtrip server, tanpa panggilan `/execute-code`.

Front-end mem-post slug sisi-aplikasi (`csharp`); `CodeExecutionController` menerjemahkan ke apa pun yang diharapkan Piston (`csharp.net`). Bahasa apa pun yang tidak ada di `piston_language_map` ditolak saat validasi:

```php
'language' => 'required|string|in:' . implode(',', array_keys($map)),
```

Untuk menambah bahasa: tambahkan ke kedua array + tambahkan opsi ke form create/edit exercise dosen (enum `exercise_language`).

---

## Bentuk request

```http
POST /execute-code
Content-Type: application/json
X-CSRF-TOKEN: …
Cookie: laravel_session=…

{
  "code": "public class Main { public static void main(String[] a) { System.out.println(\"Hi\"); } }",
  "language": "java"
}
```

Validasi:

```php
$validated = $request->validate([
    'code' => 'required|string|max:50000',           // batas ~50KB
    'language' => 'required|string|in:' . implode(',', array_keys($map)),
]);
```

Throttle: `throttle:10,1` (10/menit). Mencapai batas mengembalikan `429 Too Many Requests`. Pakai `Cache-Control: no-store` dari klien untuk menghindari 429 basi — penghitung throttle adalah per-user-per-menit.

---

## Panggilan Piston

```php
$base = rtrim((string) config('services.piston.url'), '/');
$timeout = (int) config('services.piston.timeout', 10);

$response = Http::timeout($timeout)->acceptJson()->post($base . '/execute', [
    'language' => $pistonLanguage,
    'version'  => '*',                                // versi terinstal terbaru dari bahasa itu
    'files'    => [['content' => $validated['code']]],
]);
```

`/execute` Piston mengembalikan objek JSON dengan sub-objek `run` berisi hasil runtime. Controller hanya mengekstrak field yang kita perlukan:

```php
$run = $response->json('run', []);

return response()->json([
    'stdout'    => (string) ($run['stdout'] ?? ''),
    'stderr'    => (string) ($run['stderr'] ?? ''),
    'exit_code' => (int) ($run['code'] ?? -1),
]);
```

Tidak ada compile-output, peringatan, atau data penggunaan-sumber-daya yang diteruskan. Bila Anda memerlukannya (mis. memunculkan jalur "compile error" terpisah), perluas respons — payload Piston berisi sub-objek `compile` pada bahasa yang punya langkah kompilasi.

---

## Penanganan error

```php
try {
    $response = Http::timeout($timeout)->acceptJson()->post(...);
} catch (ConnectionException | RequestException $e) {
    Log::warning('Piston request failed', ['error' => $e->getMessage()]);
    return $this->serviceUnavailable();
}

if (!$response->successful()) {
    Log::warning('Piston returned non-2xx', [
        'status' => $response->status(),
        'body'   => $response->body(),
    ]);
    return $this->serviceUnavailable();
}

// $this->serviceUnavailable() mengembalikan:
return response()->json([
    'stdout'    => '',
    'stderr'    => 'Execution service unavailable.',
    'exit_code' => -1,
], 502);
```

Klien selalu mendapat bentuk respons yang sama — `{stdout, stderr, exit_code}` — sehingga UI tak perlu meng-special-case error. 502 dengan `Execution service unavailable.` di `stderr` adalah sinyal universal "Piston mati".

---

## Kontainer Piston bawaan

`docker-compose.yml`:

```yaml
piston:
    image: ghcr.io/engineer-man/piston
    container_name: pjbl-piston
    restart: unless-stopped
    privileged: true
    tmpfs:
        - /piston/jobs
    volumes:
        - piston_packages:/piston/packages
    networks:
        - pjbl-network
```

- `privileged: true` diperlukan karena Piston memakai namespace Linux (cgroups, seccomp) untuk men-sandbox tiap eksekusi.
- `/piston/jobs` adalah `tmpfs` sehingga ruang scratch per-eksekusi berada di RAM dan hilang antar run.
- `piston_packages` adalah volume persisten — runtime terinstal bertahan setelah restart kontainer (Anda tak mengunduh ulang paket Java/PHP/C# pada tiap `docker compose down/up`).

### Memasang runtime di dalam kontainer

Secara default image Piston **tidak punya paket bahasa terinstal**. Anda memasangnya via CLI Piston:

```bash
docker compose exec piston piston ppman list
docker compose exec piston piston ppman install java=15.0.2
docker compose exec piston piston ppman install php=8.2.3
docker compose exec piston piston ppman install csharp.net=5.0.201
```

Versi mengambang; cek [Piston Public Package Index](https://github.com/engineer-man/piston/blob/master/docs/packages.md) untuk opsi terkini. Setelah install, `version: '*'` di body request memilih apa pun yang Anda pasang.

Skrip bootstrap first-boot yang memasang tiga bahasa default akan jadi tambahan yang masuk akal; belum di-commit.

---

## Variabel env

| Variabel | Default | Catatan |
|---|---|---|
| `PISTON_URL` | `http://piston:2000/api/v2` | URL internal-kontainer (di-resolve via jaringan bridge Docker). Untuk dev native tanpa kontainer, ganti ke `https://emkc.org/api/v2/piston` (instance publik Engineer-Man). |
| `PISTON_TIMEOUT` | `10` | detik sebelum `Http::timeout()` membatalkan dan controller mengembalikan 502 |

Diresolusi melalui `config/services.php`:

```php
'piston' => [
    'url' => env('PISTON_URL', 'https://emkc.org/api/v2/piston'),
    'timeout' => (int) env('PISTON_TIMEOUT', 10),
],
```

Jadi bila `PISTON_URL` tak diset, fallback publik aktif. `.env.example` menyetelnya ke URL kontainer-bawaan.

---

## Integrasi front-end

### Halaman pengerjaan exercise mahasiswa (`resources/views/mahasiswa/exercises/solve.blade.php`)

Editor CodeMirror + tombol "Run":

```js
// pseudokode — lihat code-editor.js untuk implementasi sebenarnya
async function runCode() {
    const res = await fetch('/execute-code', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            code: editor.getValue(),
            language: assignmentLanguage,    // mis. 'java'
        }),
    });

    const { stdout, stderr, exit_code } = await res.json();
    renderOutput({ stdout, stderr, exit_code });
}
```

Tombol Run **terpisah dari pengumpulan**. Menjalankan tidak membuat baris `submissions`. Tombol "Submit" mem-post ke `Mahasiswa\ExerciseController::submit` secara terpisah — lihat [submissions.md](submissions.md#pengumpulan-exercise).

### Editor exercise dosen (`resources/views/dosen/exercises/create.blade.php` dan `edit.blade.php`)

Endpoint `/execute-code` yang sama, dipakai untuk mempreview `solution_code` saat menulis. Tombol "Preview Output" memakai ulang ember throttle, jadi dosen + mahasiswa berbagi batas 10/menit pada sesi browser yang sama.

### Runtime iframe sisi-klien

Untuk `html`, `css`, `javascript`, `htmlmixed`, JS editor melewati `/execute-code` sepenuhnya dan hanya memasukkan sumber ke `<iframe srcdoc="…">` ber-sandbox. Tanpa Piston, tanpa server, tanpa throttle.

```js
if (CLIENT_SIDE_LANGUAGES.includes(language)) {
    previewIframe.srcdoc = buildPreviewHtml(editor.getValue());
} else {
    runViaPiston();
}
```

`CLIENT_SIDE_LANGUAGES = ['html', 'css', 'javascript', 'htmlmixed']` cocok dengan enum form exercise dosen dikurangi yang server-side.

---

## Catatan keamanan

- **Throttle per-user, bukan per-IP** — `throttle:10,1` memakai ID user terautentikasi. Sesi browser bersama berbagi ember.
- **Kode tidak disimpan.** `/execute-code` adalah proxy murni — ia tidak menulis ke `submissions` atau tabel lain. Hanya `Mahasiswa\ExerciseController::submit` yang mempersistensi kode (`submissions.code_answer`).
- **Piston berjalan di kontainer dengan `privileged: true`.** Ini diperlukan untuk sandboxing Piston sendiri (seccomp, namespaces). Jangan ekspos port kontainer Piston ke publik — terikat hanya ke `pjbl-network` internal.
- **Batas ukuran output** — Piston membatasi stdout pada ukuran default (≈ 64 KB per stream). Kode yang membanjiri stdout akan dipotong sisi-server; controller tak menambah batasnya sendiri.
- **Batas waktu** — `PISTON_TIMEOUT=10` membatalkan di lapisan HTTP setelah 10 detik. Piston sendiri membatasi waktu wall-clock per-eksekusi secara independen; kode berjalan-lama akan dimatikan Piston sebelum controller timeout.

---

## Mengapa tidak ada auto-grading dari proxy ini?

Alur tombol-Run untuk **iterasi interaktif**, bukan penilaian. `score` submission diset oleh dosen, bukan oleh output Piston. Handler submit mahasiswa hanya menjalankan **keyword match** (`Mahasiswa\ExerciseController::validateCode`) dan menyimpannya sebagai petunjuk di `submissions.validation_result`. Piston sama sekali tidak ada di jalur submit. Lihat [submissions.md](submissions.md#pengumpulan-exercise).

Desain WIP sebelumnya menyambungkan Piston ke jalur submit dan menyimpan output uji sebagai auto-grade. Itu di-revert. Tidak ada kolom `assignments.auto_grade`.
