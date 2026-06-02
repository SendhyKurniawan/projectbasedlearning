<?php

namespace Database\Seeders\Polimedia;

use App\Models\Department;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

/**
 * Seeds the real Politeknik Negeri Media Kreatif (PoliMedia) Jakarta academic
 * structure from database/data/polimedia.json:
 *   departments (jurusan) -> study_programs (prodi)   [global, once]
 *   -> student_classes (A..C) per prodi, per semester  [all 8 terms]
 *
 * Run after CalendarSeeder. Regenerate the JSON with:
 *   python scripts/pddikti/fetch_polimedia.py
 */
class StructureSeeder extends Seeder
{
    public function run(): void
    {
        $data = PolimediaData::load();

        // Departments + study programs are institution-wide (no semester).
        $deptIdByCode = [];
        foreach ($data['departments'] as $dept) {
            $deptIdByCode[$dept['code']] = Department::firstOrCreate(
                ['code' => $dept['code']],
                ['name' => $dept['name']],
            )->id;
        }

        $programs = [];
        foreach ($data['study_programs'] as $prodi) {
            $programs[] = StudyProgram::firstOrCreate(
                ['code' => $prodi['code']],
                [
                    'department_id' => $deptIdByCode[$prodi['department_code']],
                    'name' => $prodi['name'],
                    'level' => $prodi['level'],
                ],
            );
        }

        // Classes are per term: every semester gets A..C for every prodi.
        $semesters = Semester::with('academicYear')->get();
        $classCount = 0;

        foreach ($semesters as $semester) {
            $yearStart = (int) $semester->academicYear->year_start;
            $term = $semester->name;

            foreach ($programs as $program) {
                $abbr = PolimediaData::abbr($program->name);

                foreach (PolimediaData::KELAS_LETTERS as $letter) {
                    StudentClass::firstOrCreate([
                        'study_program_id' => $program->id,
                        'semester_id' => $semester->id,
                        'name' => PolimediaData::className($abbr, $letter, $yearStart, $term),
                    ]);
                    $classCount++;
                }
            }
        }

        $this->command->info(sprintf(
            'PoliMedia structure: %d departments, %d prodi, %d classes across %d semesters.',
            count($data['departments']),
            count($programs),
            $classCount,
            $semesters->count(),
        ));
    }
}
