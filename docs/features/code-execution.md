# Code Execution Sandbox

## What it is

A server-side proxy that lets mahasiswa "Run" their code from the exercise solve page (and dosen "Preview" from the exercise editor) without giving the browser direct access to a runtime. The proxy forwards the request to a **Piston** instance — by default the bundled `piston` docker container — and returns `{stdout, stderr, exit_code}`.

- **Controller**: `App\Http\Controllers\CodeExecutionController` (auth-only, throttled)
- **Route**: `POST /execute-code` (`execute.code`), middleware `auth + throttle:10,1` (10 requests per minute per user)
- **Config**: `config/code_execution.php` (allowed languages) + `config/services.php` (Piston URL/timeout)
- **Bundled runtime**: `ghcr.io/engineer-man/piston` docker container

```php
// routes/web.php (inside the auth-only group)
Route::post('/execute-code', [CodeExecutionController::class, 'execute'])
    ->name('execute.code')
    ->middleware('throttle:10,1');
```

---

## Language allowlist

`config/code_execution.php`:

```php
return [
    'server_side_languages' => ['java', 'php', 'csharp'],

    'piston_language_map' => [
        'java'   => 'java',
        'php'    => 'php',
        'csharp' => 'csharp.net',     // Piston runs Mono for C#
    ],
];
```

- **Server-side languages** (`java`, `php`, `csharp`) → routed through `/execute-code` → Piston.
- **Client-side languages** (`html`, `css`, `javascript`, `htmlmixed`) → rendered in a sandboxed `<iframe>` directly in the browser; no server roundtrip, no `/execute-code` call.

The frontend posts the app-side slug (`csharp`); `CodeExecutionController` translates to whatever Piston expects (`csharp.net`). Any language not in `piston_language_map` is rejected at validation time:

```php
'language' => 'required|string|in:' . implode(',', array_keys($map)),
```

To add a language: append to both arrays + add the option to the dosen exercise create/edit form (`exercise_language` enum).

---

## Request shape

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

Validation:

```php
$validated = $request->validate([
    'code' => 'required|string|max:50000',           // ~50KB cap
    'language' => 'required|string|in:' . implode(',', array_keys($map)),
]);
```

Throttle: `throttle:10,1` (10/min). Hitting the cap returns `429 Too Many Requests`. Use `Cache-Control: no-store` from the client to avoid stale 429s — the throttle counter is per-user-per-minute.

---

## Piston call

```php
$base = rtrim((string) config('services.piston.url'), '/');
$timeout = (int) config('services.piston.timeout', 10);

$response = Http::timeout($timeout)->acceptJson()->post($base . '/execute', [
    'language' => $pistonLanguage,
    'version'  => '*',                                // latest installed version of that language
    'files'    => [['content' => $validated['code']]],
]);
```

Piston's `/execute` returns a JSON object with a `run` sub-object containing the runtime result. The controller extracts only the fields we care about:

```php
$run = $response->json('run', []);

return response()->json([
    'stdout'    => (string) ($run['stdout'] ?? ''),
    'stderr'    => (string) ($run['stderr'] ?? ''),
    'exit_code' => (int) ($run['code'] ?? -1),
]);
```

No compile-output, no warnings, no resource-usage data is forwarded. If you need them (e.g. to surface a separate "compile error" lane), extend the response — the Piston payload contains a `compile` sub-object on languages that have a compile step.

---

## Error handling

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

// $this->serviceUnavailable() returns:
return response()->json([
    'stdout'    => '',
    'stderr'    => 'Execution service unavailable.',
    'exit_code' => -1,
], 502);
```

The client always gets the same response shape — `{stdout, stderr, exit_code}` — so the UI doesn't need to special-case errors. A 502 with `Execution service unavailable.` in `stderr` is the universal "Piston is down" signal.

---

## Bundled Piston container

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

- `privileged: true` is required because Piston uses Linux namespaces (cgroups, seccomp) for sandboxing each execution.
- `/piston/jobs` is `tmpfs` so per-execution scratch space is in RAM and disappears between runs.
- `piston_packages` is a persistent volume — installed runtimes survive container restarts (you don't re-download the Java/PHP/C# packages on every `docker compose down/up`).

### Installing runtimes inside the container

Out of the box the Piston image has **no language packages installed**. You install them via Piston's CLI:

```bash
docker compose exec piston piston ppman list
docker compose exec piston piston ppman install java=15.0.2
docker compose exec piston piston ppman install php=8.2.3
docker compose exec piston piston ppman install csharp.net=5.0.201
```

Versions float; check the [Piston Public Package Index](https://github.com/engineer-man/piston/blob/master/docs/packages.md) for the current options. After install, the `version: '*'` in the request body picks whatever you installed.

A first-boot bootstrap script that installs the three default languages would be a sensible addition; not committed yet.

---

## Env vars

| Variable | Default | Notes |
|---|---|---|
| `PISTON_URL` | `http://piston:2000/api/v2` | Container-internal URL (resolves via the Docker bridge network). For native dev without the container, swap to `https://emkc.org/api/v2/piston` (the public Engineer-Man instance). |
| `PISTON_TIMEOUT` | `10` | seconds before `Http::timeout()` aborts and the controller returns 502 |

Resolved through `config/services.php`:

```php
'piston' => [
    'url' => env('PISTON_URL', 'https://emkc.org/api/v2/piston'),
    'timeout' => (int) env('PISTON_TIMEOUT', 10),
],
```

So if `PISTON_URL` is unset, the public fallback kicks in. The `.env.example` sets it to the bundled-container URL.

---

## Frontend integration

### Mahasiswa exercise solve page (`resources/views/mahasiswa/exercises/solve.blade.php`)

CodeMirror editor + a "Run" button:

```js
// pseudocode — see code-editor.js for the real impl
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
            language: assignmentLanguage,    // e.g. 'java'
        }),
    });

    const { stdout, stderr, exit_code } = await res.json();
    renderOutput({ stdout, stderr, exit_code });
}
```

The Run button is **decoupled from submission**. Running does not create a `submissions` row. The "Submit" button posts to `Mahasiswa\ExerciseController::submit` separately — see [submissions.md](submissions.md#exercise-submission).

### Dosen exercise editor (`resources/views/dosen/exercises/create.blade.php` and `edit.blade.php`)

Same `/execute-code` endpoint, used to preview the `solution_code` while authoring. The "Preview Output" button reuses the throttle bucket, so dosen + mahasiswa share the 10/min cap on the same browser session.

### Client-side iframe runtimes

For `html`, `css`, `javascript`, `htmlmixed`, the editor JS skips `/execute-code` entirely and just feeds the source into a sandboxed `<iframe srcdoc="…">`. No Piston, no server, no throttle.

```js
if (CLIENT_SIDE_LANGUAGES.includes(language)) {
    previewIframe.srcdoc = buildPreviewHtml(editor.getValue());
} else {
    runViaPiston();
}
```

`CLIENT_SIDE_LANGUAGES = ['html', 'css', 'javascript', 'htmlmixed']` matches the dosen exercise form's enum minus the server-side ones.

---

## Security notes

- **Throttle is per-user, not per-IP** — `throttle:10,1` uses the authenticated user's ID. A shared browser session shares the bucket.
- **Code is not stored.** `/execute-code` is a pure proxy — it does not write to `submissions` or any other table. Only `Mahasiswa\ExerciseController::submit` persists code (`submissions.code_answer`).
- **Piston runs in a container with `privileged: true`.** This is required for Piston's own sandboxing (seccomp, namespaces). Don't expose the Piston container's port to the public — it's bound to the internal `pjbl-network` only.
- **Output size limits** — Piston caps stdout at a default size (≈ 64 KB per stream). Code that floods stdout will be truncated server-side; the controller doesn't add its own cap.
- **Time limit** — `PISTON_TIMEOUT=10` aborts at the HTTP layer after 10 s. Piston itself caps per-execution wall-clock time independently; long-running code will be killed by Piston before the controller times out.

---

## Why no auto-grading from this proxy?

The Run-button flow is for **interactive iteration**, not grading. A submission's `score` is set by the dosen, not by Piston output. The mahasiswa submit handler runs only a **keyword match** (`Mahasiswa\ExerciseController::validateCode`) and stores it as a hint in `submissions.validation_result`. Piston is not in the submit path at all. See [submissions.md](submissions.md#exercise-submission).

An earlier WIP design wired Piston into the submit path and stored the test output as an auto-grade. It was reverted. There is no `assignments.auto_grade` column.
