import { test, expect } from '@playwright/test';
import { IDS, USERS } from './fixtures';
import { loginAs } from './helpers/auth';

test.describe('Flow 2 — Admin · Academic (ACAD)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'admin');
  });

  test('ACAD-01 AcademicYear list shows seeded year', async ({ page }) => {
    await page.goto('/admin/academic-years');
    await expect(page.locator('tbody')).toContainText('2024');
    await expect(page.locator('tbody')).toContainText('2025');
  });

  test('ACAD-02 AcademicYear create succeeds', async ({ page }) => {
    await page.goto('/admin/academic-years/create');
    await page.locator('#year_start').fill('2030');
    await page.locator('#year_end').fill('2031');
    await page.getByRole('button', { name: /^simpan$/i }).click();
    await expect(page).toHaveURL(/\/admin\/academic-years/);
    await expect(page.locator('tbody')).toContainText('2030');
  });

  test('ACAD-03 Semester list shows seeded semester', async ({ page }) => {
    await page.goto('/admin/semesters');
    await expect(page.locator('tbody')).toContainText('Ganjil');
  });

  test('ACAD-04 Semester create succeeds', async ({ page }) => {
    await page.goto('/admin/semesters/create');
    await page.locator('#academic_year_id').selectOption({ index: 0 });
    await page.locator('#name').selectOption('Genap');
    await page.locator('#start_date').fill('2025-02-01');
    await page.locator('#end_date').fill('2025-07-31');
    await page.getByRole('button', { name: /^simpan$/i }).click();
    await expect(page).toHaveURL(/\/admin\/semesters/);
    await expect(page.locator('tbody')).toContainText('Genap');
  });

  test('ACAD-05 Course CRUD: create succeeds', async ({ page }) => {
    const stamp = Date.now();
    await page.goto('/admin/courses/create');
    await page.locator('#kode_matkul').fill('PW' + String(stamp).slice(-4));
    await page.locator('#nama_matkul').fill('PW Matkul ' + stamp);
    await page.locator('#dosen_id').selectOption(String(USERS.dosen.id));
    await page.locator('#semester_id').selectOption(String(IDS.semester));
    await page.getByRole('button', { name: /create course/i }).click();
    await expect(page).toHaveURL(/\/admin\/courses(\?|$)/);
    await expect(page.locator('tbody')).toContainText('PW Matkul ' + stamp);
  });

  test('ACAD-06 Enroll a student into a course', async ({ page }) => {
    await page.goto(`/admin/courses/${IDS.course.if202}/edit`);
    const select = page.locator('select[name="student_id"]');
    const optionCount = await select.locator('option').count();
    test.skip(optionCount < 2, 'No available students to enroll');
    await select.selectOption({ index: 1 });
    await page.getByRole('button', { name: /enroll/i }).click();
    await expect(page.locator('h3', { hasText: /Enrolled Students/ })).toBeVisible();
  });

  test('ACAD-07 Grades overview table loads with seeded course', async ({ page }) => {
    await page.goto('/admin/grades');
    await expect(page.locator('h2')).toContainText(/Rekap Nilai/i);
    await expect(page.locator('body')).toContainText(/Algoritma Dusk|library_books/);
  });

  test('ACAD-08 Admin push-debug page renders', async ({ page }) => {
    await page.goto('/admin/debug/push');
    await expect(page.locator('h2')).toContainText(/Debug Push Notifications/i);
    await expect(page.locator('#user_id')).toBeVisible();
  });
});
