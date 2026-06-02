"""
Fetch Politeknik Negeri Media Kreatif (PoliMedia) Jakarta academic structure
from PDDIKTI (https://github.com/IlhamriSKY/PDDIKTI-kemdikbud-API) and write it
to database/data/polimedia.json for the Laravel seeder to consume.

PDDIKTI exposes *prodi* (study programs) but has no *jurusan* (department)
endpoint, so prodi are grouped into departments with a keyword map below.
Admin can rename departments in the app later.

Usage:
    pip install -r scripts/pddikti/requirements.txt
    python scripts/pddikti/fetch_polimedia.py

The seeder reads the committed JSON and never needs network at deploy time.
"""

from __future__ import annotations

import json
import sys
from pathlib import Path

PT_QUERY = "Politeknik Negeri Media Kreatif"
PT_MATCH = "media kreatif"          # substring used to pick the right PT row
TAHUN_CANDIDATES = [20251, 20242, 20241, 20231, 20232]  # YYYYS, newest first
OUT_PATH = Path(__file__).resolve().parents[2] / "database" / "data" / "polimedia.json"

# Keep the Jakarta main campus only; drop off-campus PSDKU units (Makassar/Medan).
SKIP_NAME_SUBSTR = ["kampus kota"]

# prodi name keyword -> department (jurusan). First match wins; order matters.
JURUSAN_RULES = [
    ("Teknik Grafika dan Penerbitan", "TGP", [
        "grafika", "cetak", "kemasan", "pengemasan", "penerbitan", "industri"]),
    ("Penyiaran dan Komunikasi", "PK", [
        "penyiaran", "broadcast", "produksi media", "periklanan",
        "jurnalistik", "komunikasi", "advertising", "televisi", "film"]),
    ("Desain", "DSN", [
        "desain", "animasi", "game", "permainan", "multimedia",
        "fotografi", "mode", "interior"]),
]
FALLBACK_DEPT = ("Program Studi Lainnya", "LAIN")


def classify(nama_prodi: str) -> tuple[str, str]:
    low = nama_prodi.lower()
    for name, code, keywords in JURUSAN_RULES:
        if any(k in low for k in keywords):
            return name, code
    return FALLBACK_DEPT


def as_list(payload) -> list:
    """PDDIKTI helpers may return a list, or a dict wrapping one under
    'data'/'results'/'mahasiswa' etc. Normalise to a list of dicts."""
    if payload is None:
        return []
    if isinstance(payload, list):
        return payload
    if isinstance(payload, dict):
        for key in ("data", "results", "pt", "prodi"):
            if isinstance(payload.get(key), list):
                return payload[key]
        # single object
        return [payload]
    return []


def pick(d: dict, *keys, default=""):
    for k in keys:
        if k in d and d[k] not in (None, ""):
            return d[k]
    return default


def level_from_jenjang(jenjang: str) -> str:
    j = (jenjang or "").upper().replace("-", "").replace(" ", "")
    for lvl in ("D1", "D2", "D3", "D4", "S1", "S2", "S3"):
        if lvl in j:
            return lvl
    if "SARJANA TERAPAN" in (jenjang or "").upper():
        return "D4"
    return "D4"


def main() -> int:
    try:
        from pddiktipy import api
    except ImportError:
        print("pddiktipy not installed. Run: pip install -r scripts/pddikti/requirements.txt",
              file=sys.stderr)
        return 2

    with api() as client:
        pts = as_list(client.search_pt(PT_QUERY))
        if not pts:
            print(f"No PT found for '{PT_QUERY}'", file=sys.stderr)
            return 1

        pt = next((p for p in pts if PT_MATCH in str(pick(p, "nama")).lower()), pts[0])
        pt_id = pick(pt, "id", "id_sp", "id_pt")
        print(f"PT: {pick(pt, 'nama')} (id={pt_id})", file=sys.stderr)

        detail = client.get_detail_pt(pt_id) or {}
        if isinstance(detail, list) and detail:
            detail = detail[0]

        prodi_rows: list = []
        used_tahun = None
        for tahun in TAHUN_CANDIDATES:
            rows = as_list(client.get_prodi_pt(pt_id, tahun))
            if rows:
                prodi_rows, used_tahun = rows, tahun
                break
        print(f"prodi count={len(prodi_rows)} (tahun={used_tahun})", file=sys.stderr)

    if not prodi_rows:
        print("No prodi returned for any candidate tahun.", file=sys.stderr)
        return 1

    departments: dict[str, dict] = {}
    study_programs: list[dict] = []
    seen_codes: set[str] = set()

    for r in prodi_rows:
        nama = str(pick(r, "nama_prodi", "nama")).strip()
        if not nama:
            continue
        low_name = nama.lower()
        if any(s in low_name for s in SKIP_NAME_SUBSTR):
            continue  # off-campus PSDKU unit, keep Jakarta only
        status = str(pick(r, "status_prodi", "status", default="A")).upper()
        if status.startswith("TIDAK") or status in ("N", "NONAKTIF", "TUTUP"):
            continue

        dept_name, dept_code = classify(nama)
        departments.setdefault(dept_code, {"name": dept_name, "code": dept_code})

        jenjang = str(pick(r, "jenjang_prodi", "jenjang"))
        code = str(pick(r, "kode_prodi")).strip()
        if not code or code in seen_codes:
            code = f"{dept_code}{len(study_programs)+1:02d}"
        seen_codes.add(code)

        study_programs.append({
            "department_code": dept_code,
            "name": nama,
            "code": code,
            "level": level_from_jenjang(jenjang),
            "akreditasi": pick(r, "akreditasi", default=None),
        })

    out = {
        "pt": {
            "id": pt_id,
            "nama": pick(pt, "nama"),
            "kode": pick(detail, "kode_pt", "kode"),
            "akreditasi": pick(detail, "akreditasi_pt", "akreditasi", default=None),
            "tahun": used_tahun,
        },
        "departments": list(departments.values()),
        "study_programs": study_programs,
    }

    OUT_PATH.parent.mkdir(parents=True, exist_ok=True)
    OUT_PATH.write_text(json.dumps(out, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"Wrote {len(study_programs)} prodi in {len(departments)} departments -> {OUT_PATH}",
          file=sys.stderr)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
