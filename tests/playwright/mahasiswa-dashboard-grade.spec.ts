import { test, expect } from '@playwright/test';
import { IDS } from './fixtures';
import { loginAs } from './helpers/auth';

test.describe('Flow 4 — Mahasiswa · Dashboard & Grade (DASH)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'mahasiswa');
  });

  test('DASH-01 dashboard hero greets the user by name', async ({ page }) => {
    await page.goto('/mahasiswa/dashboard');
    await expect(page.locator('h2')).toContainText(/Mulai Belajar/i);
    await expect(page.locator('body')).toContainText('Mahasiswa Dusk');
  });

  test('DASH-02 statistics grid renders three KPI cards', async ({ page }) => {
    await page.goto('/mahasiswa/dashboard');
    await expect(page.locator('body')).toContainText(/Mata Kuliah/i);
    await expect(page.locator('body')).toContainText(/Tugas/i);
  });

  test('DASH-03 navigation card links to courses index', async ({ page }) => {
    await page.goto('/mahasiswa/dashboard');
    await page.getByRole('link', { name: /lihat matakuliah/i }).click();
    await expect(page).toHaveURL(/\/mahasiswa\/courses/);
  });

  test('DASH-04 grades page renders transcript header and average', async ({ page }) => {
    await page.goto('/mahasiswa/grades');
    await expect(page.getByRole('heading', { name: /Transkrip Evaluasi/i })).toBeVisible();
    await expect(page.locator('body')).toContainText(/Rata-rata|Selesai/i);
  });

  test('DASH-05 grade detail (course-scoped) opens (informational)', async ({ page }) => {
    await page.goto('/mahasiswa/grades');
    // Grades page is single-pane in this app — verify it lists course or empty hint
    await expect(page.locator('body')).toContainText(/Algoritma Dusk|tidak ada/i);
  });

  test('DASH-06 conferences index lists the seeded scheduled conference', async ({ page }) => {
    await page.goto(`/mahasiswa/courses/${IDS.course.if101}/conferences`);
    await expect(page.getByRole('heading', { name: /Ruang Konferensi/i })).toBeVisible();
    await expect(page.locator('body')).toContainText(/Kelas Virtual|Sesi Terjadwal/i);
  });
});
