<?php

namespace Database\Seeders\Polimedia;

use Illuminate\Support\Str;

/**
 * Shared helpers for the PoliMedia seeders: loads the fetched structure JSON,
 * derives prodi abbreviations, and defines the 4-year academic calendar plus the
 * demo-class constants used to give testers a fully-populated example to land on.
 *
 * The calendar is 4 academic years x (Ganjil, Genap) = 8 semesters; only the
 * latest Ganjil is active. Departments/prodi/dosen are global; classes, students,
 * courses and content are seeded per-semester.
 */
class PolimediaData
{
    /** Prodi code that gets named handout accounts + sample submissions (active term, kelas A). */
    public const DEMO_PRODI_CODE = '90442';   // Desain Grafis (D3)
    public const DEMO_KELAS_INDEX = 0;        // first kelas (A)

    /** Shared password for every pre-made account (documented for testers). */
    public const SHARED_PASSWORD = 'password';

    public const EMAIL_DOMAIN = 'polimedia.test';
    public const STUDENT_EMAIL_DOMAIN = 'student.polimedia.test';

    // 4-year calendar: 4 academic years x (Ganjil, Genap) = 8 semesters.
    public const ACADEMIC_YEARS = [[2022, 2023], [2023, 2024], [2024, 2025], [2025, 2026]];
    public const TERMS = ['Ganjil', 'Genap'];
    public const ACTIVE_YEAR_START = 2025;
    public const ACTIVE_TERM = 'Ganjil';

    // Per prodi, per term sizing.
    public const KELAS_LETTERS = ['A', 'B', 'C'];
    public const STUDENTS_PER_KELAS = 8;

    private static ?array $cache = null;

    public static function isDemoProdi(string $prodiCode): bool
    {
        return $prodiCode === self::DEMO_PRODI_CODE;
    }

    /**
     * Deterministic dosen email per prodi so the course seeder can find the
     * right lecturer. The demo prodi's dosen gets the friendly handout address.
     */
    public static function dosenEmail(string $prodiCode): string
    {
        return self::isDemoProdi($prodiCode)
            ? 'dosen@' . self::EMAIL_DOMAIN
            : "dosen.{$prodiCode}@" . self::EMAIL_DOMAIN;
    }

    public static function isActiveTerm(int $yearStart, string $term): bool
    {
        return $yearStart === self::ACTIVE_YEAR_START && $term === self::ACTIVE_TERM;
    }

    /** Chronological study-level 1..8 for a (year, term). 2022 Ganjil=1 … 2025 Genap=8. */
    public static function level(int $yearStart, string $term): int
    {
        $yearIdx = $yearStart - self::ACADEMIC_YEARS[0][0];
        return $yearIdx * 2 + ($term === 'Ganjil' ? 1 : 2);
    }

    /** @return array{0:string,1:string} [start_date, end_date] */
    public static function termDates(int $yearStart, int $yearEnd, string $term): array
    {
        return $term === 'Ganjil'
            ? ["{$yearStart}-08-01", "{$yearEnd}-01-31"]
            : ["{$yearEnd}-02-01", "{$yearEnd}-07-31"];
    }

    /** Kelas label disambiguated by term, e.g. "DG-A 25Gj". */
    public static function className(string $abbr, string $letter, int $yearStart, string $term): string
    {
        $yy = substr((string) $yearStart, 2, 2);
        $t = $term === 'Ganjil' ? 'Gj' : 'Gn';

        return "{$abbr}-{$letter} {$yy}{$t}";
    }

    public static function roman(int $n): string
    {
        return [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII'][$n] ?? (string) $n;
    }

    /** Absolute path to the committed structure file. */
    public static function path(): string
    {
        return database_path('data/polimedia.json');
    }

    /** @return array{pt:array,departments:array,study_programs:array} */
    public static function load(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $path = self::path();
        if (! is_file($path)) {
            throw new \RuntimeException(
                "Missing {$path}. Generate it with: python scripts/pddikti/fetch_polimedia.py"
            );
        }

        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $data['departments'] ??= [];
        $data['study_programs'] ??= [];

        return self::$cache = $data;
    }

    /**
     * Short uppercase abbreviation for a prodi name, used for kelas labels and
     * course codes. "Desain Grafis" -> "DG", "Animasi" -> "ANI".
     */
    public static function abbr(string $name): string
    {
        $name = trim(preg_replace('/\(.*?\)/', '', $name));
        $stop = ['dan', 'di', 'the', 'of', '&'];
        $words = array_filter(
            preg_split('/[\s,]+/', $name),
            fn ($w) => $w !== '' && ! in_array(Str::lower($w), $stop, true),
        );

        if (count($words) === 1) {
            return Str::upper(Str::substr(reset($words), 0, 3));
        }

        $abbr = '';
        foreach ($words as $w) {
            $abbr .= Str::upper(Str::substr($w, 0, 1));
        }

        return $abbr;
    }
}
