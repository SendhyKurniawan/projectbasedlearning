<?php

namespace Database\Seeders\Polimedia;

use App\Models\Department;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

/**
 * Mengisi struktur akademik nyata PoliMedia (Politeknik Negeri Media Kreatif) Jakarta
 * dari database/data/polimedia.json:
 *   jurusan (departments) -> program studi (study_programs)  [global, sekali]
 *   -> kelas (student_classes, A..C) per prodi, per semester  [semua 8 term]
 *
 * Dijalankan setelah CalendarSeeder. Regenerasi JSON dengan:
 *   python scripts/pddikti/fetch_polimedia.py
 */
class StructureSeeder extends Seeder
{
    public function run(): void
    {
        $data = PolimediaData::load();

        // Jurusan + prodi berlaku se-institusi (tidak terikat semester).
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

        // Kelas dibuat per term: tiap semester mendapat kelas A..C untuk tiap prodi.
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
