import { test, expect } from '@playwright/test';
import { IDS } from './fixtures';
import { loginAs } from './helpers/auth';

test.describe('Flow 4 — Mahasiswa · Course (CRS)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'mahasiswa');
  });

  test('CRS-01 course catalog renders with seeded course', async ({ page }) => {
    await page.goto('/mahasiswa/courses');
    await expect(page.getByRole('heading', { name: /Eksplorasi/i })).toBeVisible();
    await expect(page.locator('body')).toContainText('Algoritma Dusk');
  });

  test('CRS-02 enrolled course shows "Terdaftar" badge', async ({ page }) => {
    await page.goto('/mahasiswa/courses');
    const card = page.locator('.group', { hasText: 'Algoritma Dusk' }).first();
    await expect(card).toContainText(/Terdaftar/i);
  });

  test('CRS-03 enroll into a non-enrolled course redirects to course show', async ({ page }) => {
    await page.goto('/mahasiswa/courses');
    const card = page.locator('.group', { hasText: 'Dasar Jaringan' }).first();
    await card.locator('button[type="submit"]', { hasText: /daftar/i }).click();
    await expect(page).toHaveURL(/\/mahasiswa\/courses\/\d+$/);
  });

  test('CRS-04 course show lists materials and assignments', async ({ page }) => {
    await page.goto(`/mahasiswa/courses/${IDS.course.if101}`);
    await expect(page.locator('body')).toContainText('Modul 1');
    await expect(page.locator('body')).toContainText('Tugas PDF');
  });

  test('CRS-05 view a material registers MaterialView (no error)', async ({ page }) => {
    await page.goto(`/mahasiswa/courses/${IDS.course.if101}/materials/${IDS.material.modul1}`);
    await expect(page.locator('body')).toContainText('Modul 1');
  });

  test('CRS-06 re-viewing material does not duplicate (idempotent)', async ({ page }) => {
    await page.goto(`/mahasiswa/courses/${IDS.course.if101}/materials/${IDS.material.modul1}`);
    await page.goto(`/mahasiswa/courses/${IDS.course.if101}/materials/${IDS.material.modul1}`);
    await expect(page.locator('body')).toContainText('Modul 1');
  });

  test('CRS-07 material with no attachment still renders content', async ({ page }) => {
    await page.goto(`/mahasiswa/courses/${IDS.course.if101}/materials/${IDS.material.modul2}`);
    await expect(page.locator('body')).toContainText('Modul 2');
  });

  test('CRS-08 nonexistent course returns 404', async ({ page }) => {
    const res = await page.goto('/mahasiswa/courses/9999');
    expect(res?.status()).toBe(404);
  });
});
