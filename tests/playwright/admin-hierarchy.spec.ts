import { test, expect } from '@playwright/test';
import { IDS } from './fixtures';
import { loginAs } from './helpers/auth';

test.describe('Flow 2 — Admin · Hierarchy (HIER)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'admin');
  });

  test('HIER-01 Department CRUD: create department', async ({ page }) => {
    const stamp = Date.now();
    const code = 'PW' + String(stamp).slice(-3);
    await page.goto('/admin/departments/create');
    await page.locator('#code').fill(code);
    await page.locator('#name').fill('PW Dept ' + stamp);
    await page.getByRole('button', { name: /simpan/i }).click();
    await expect(page).toHaveURL(/\/admin\/departments(\?|$)/);
    await expect(page.locator('tbody')).toContainText('PW Dept ' + stamp);
  });

  test('HIER-02 Department CRUD: edit department', async ({ page }) => {
    await page.goto('/admin/departments');
    const editLink = page.locator('a', { hasText: /^edit$/i }).first();
    await editLink.click();
    await page.locator('#name').fill('Edited Dept');
    await page.getByRole('button', { name: /perbarui|simpan/i }).click();
    await expect(page).toHaveURL(/\/admin\/departments(\?|$)/);
    await expect(page.locator('tbody')).toContainText('Edited Dept');
  });

  test('HIER-03 StudyProgram CRUD: create new program', async ({ page }) => {
    const stamp = Date.now();
    const code = 'PWP' + String(stamp).slice(-3);
    await page.goto('/admin/study-programs/create');
    await page.locator('#department_id').selectOption({ index: 1 });
    await page.locator('#level').selectOption('S1');
    await page.locator('#code').fill(code);
    await page.locator('#name').fill('PW Prodi ' + stamp);
    await page.getByRole('button', { name: /simpan/i }).click();
    await expect(page).toHaveURL(/\/admin\/study-programs(\?|$)/);
    await expect(page.locator('tbody')).toContainText('PW Prodi ' + stamp);
  });

  test('HIER-04 StudyProgram list shows seeded program', async ({ page }) => {
    await page.goto('/admin/study-programs');
    await expect(page.locator('tbody')).toContainText('S1 Informatika');
  });

  test('HIER-05 StudentClass CRUD: create class', async ({ page }) => {
    const stamp = Date.now();
    await page.goto('/admin/student-classes/create');
    await page.locator('#study_program_id').selectOption({ index: 1 });
    await page.locator('#semester_id').selectOption({ index: 1 });
    await page.locator('#name').fill('PW-Kelas-' + stamp);
    await page.getByRole('button', { name: /simpan/i }).click();
    await expect(page).toHaveURL(/\/admin\/student-classes(\?|$)/);
    await expect(page.locator('tbody')).toContainText('PW-Kelas-' + stamp);
  });

  test('HIER-06 StudentClass list shows seeded class', async ({ page }) => {
    await page.goto('/admin/student-classes');
    await expect(page.locator('tbody')).toContainText('Kelas A');
  });

  test('HIER-07 Drill-down departments index renders cards', async ({ page }) => {
    await page.goto('/admin/hierarchy/departments');
    await expect(page.getByRole('heading', { name: /Pilih Jurusan/i })).toBeVisible();
    await expect(page.locator('body')).toContainText('Teknik Informatika');
  });

  test('HIER-08 Drill-down department → study programs', async ({ page }) => {
    await page.goto(`/admin/hierarchy/departments/${IDS.department}`);
    await expect(page.getByRole('heading', { name: /Pilih Program Studi/i })).toBeVisible();
    await expect(page.locator('body')).toContainText('S1 Informatika');
  });

  test('HIER-09 Drill-down study program → semesters', async ({ page }) => {
    await page.goto(`/admin/hierarchy/study-programs/${IDS.studyProgram}/semesters`);
    await expect(page.getByRole('heading', { name: /Pilih Semester/i })).toBeVisible();
    await expect(page.locator('body')).toContainText('Ganjil');
  });

  test('HIER-10 Drill-down semester → classes & courses panel', async ({ page }) => {
    await page.goto(`/admin/hierarchy/study-programs/${IDS.studyProgram}/semesters/${IDS.semester}`);
    await expect(page.locator('h3', { hasText: /Daftar Kelas/ })).toBeVisible();
    await expect(page.locator('h3', { hasText: /Mata Kuliah Semester/ })).toBeVisible();
    await expect(page.locator('body')).toContainText('Kelas A');
  });

  test('HIER-11 Add semester course (drill-down)', async ({ page }) => {
    const stamp = Date.now();
    await page.goto(`/admin/hierarchy/study-programs/${IDS.studyProgram}/semesters/${IDS.semester}`);
    await page.getByRole('button', { name: /\+ Tambah Mata Kuliah/i }).click();
    await page.locator('#kode_matkul').fill('PW' + String(stamp).slice(-4));
    await page.locator('#nama_matkul').fill('PW Course ' + stamp);
    await page.locator('#sks').fill('3');
    await page.locator('#dosen_id').selectOption({ index: 1 });
    await page.getByRole('button', { name: /simpan course/i }).click();
    await expect(page.locator('body')).toContainText('PW Course ' + stamp);
  });

  test('HIER-12 Class detail shows students and courses with breadcrumbs', async ({ page }) => {
    await page.goto(`/admin/hierarchy/student-classes/${IDS.studentClass}`);
    await expect(page.locator('body')).toContainText('Daftar Mahasiswa');
    await expect(page.locator('body')).toContainText('Mata Kuliah Semester');
    await expect(page.locator('header, nav, .flex').first()).toContainText(/Data Akademik/i);
    await expect(page.locator('body')).toContainText('Mahasiswa Dusk');
  });
});
