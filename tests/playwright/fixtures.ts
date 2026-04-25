/**
 * Canonical fixtures mirroring database/seeders/DuskSeeder.php.
 * Any change to the seeder MUST be reflected here.
 */

export type Role = 'admin' | 'dosen' | 'mahasiswa' | 'pendingDosen' | 'otherMahasiswa' | 'otherDosen';

export interface SeedUser {
  id: number;
  name: string;
  email: string;
  password: string;
  role: 'admin' | 'dosen' | 'mahasiswa';
  active: boolean;
}

export const USERS: Record<Role, SeedUser> = {
  admin:           { id: 1, name: 'Admin Dusk',       email: 'admin@pjbl.test',       password: 'password', role: 'admin',     active: true  },
  dosen:           { id: 2, name: 'Dosen Dusk',       email: 'dosen@pjbl.test',       password: 'password', role: 'dosen',     active: true  },
  mahasiswa:       { id: 3, name: 'Mahasiswa Dusk',   email: 'mahasiswa@pjbl.test',   password: 'password', role: 'mahasiswa', active: true  },
  pendingDosen:    { id: 4, name: 'Pending Dosen',    email: 'pending@pjbl.test',     password: 'password', role: 'dosen',     active: false },
  otherMahasiswa:  { id: 5, name: 'Other Mahasiswa',  email: 'other@pjbl.test',       password: 'password', role: 'mahasiswa', active: true  },
  otherDosen:      { id: 6, name: 'Other Dosen',      email: 'other.dosen@pjbl.test', password: 'password', role: 'dosen',     active: true  },
};

export const IDS = {
  academicYear: 1,
  semester: 1,
  department: 1,
  studyProgram: 1,
  studentClass: 1,
  course: {
    if101: 1,
    if202: 2,
  },
  material: {
    modul1: 1,
    modul2: 2,
  },
  assignment: {
    tugasPdf: 1,
    tugasUrl: 2,
    tugasTerkunci: 3,
    quizCepat: 4,
    exerciseHtml: 5,
  },
  quizQuestion: {
    mc: 1,
    essay: 2,
  },
  conference: {
    kelasVirtual1: 1,
  },
} as const;

export const DASHBOARD_URL: Record<SeedUser['role'], string> = {
  admin: '/admin/dashboard',
  dosen: '/dosen/dashboard',
  mahasiswa: '/mahasiswa/dashboard',
};
