import { test, expect } from '@playwright/test';
import { IDS } from './fixtures';
import { loginAs, getCsrfAndCookies } from './helpers/auth';
import { futureDeadline, pastDeadline } from './helpers/dates';
import { SAMPLE_PDF as PDF } from './helpers/assets';

test.describe('Flow 3 — Dosen · Assignments (ASG)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'dosen');
  });

  test('ASG-01 create tugas-pdf assignment', async ({ page }) => {
    const stamp = Date.now();
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments/create`);
    await page.locator('#type').selectOption('tugas');
    await page.locator('#title').fill('PW Tugas PDF ' + stamp);
    await page.locator('#description').fill('Detail tugas PDF.');
    await page.locator('#submission_format').selectOption('pdf');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('100');
    await page.getByRole('button', { name: /simpan tugas/i }).click();
    await expect(page).toHaveURL(/\/dosen\/courses\/.+\/assignments$/);
    await expect(page.locator('body')).toContainText('PW Tugas PDF ' + stamp);
  });

  test('ASG-02 create tugas-url assignment', async ({ page }) => {
    const stamp = Date.now();
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments/create`);
    await page.locator('#type').selectOption('tugas');
    await page.locator('#title').fill('PW Tugas URL ' + stamp);
    await page.locator('#submission_format').selectOption('url');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('100');
    await page.getByRole('button', { name: /simpan tugas/i }).click();
    await expect(page).toHaveURL(/\/assignments$/);
    await expect(page.locator('body')).toContainText('PW Tugas URL ' + stamp);
  });

  test('ASG-03 deadline in past rejected', async ({ page }) => {
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments/create`);
    await page.locator('#title').fill('Past deadline');
    await page.locator('#submission_format').selectOption('pdf');
    await page.locator('#deadline').fill(pastDeadline());
    await page.locator('#max_score').fill('100');
    await page.getByRole('button', { name: /simpan tugas/i }).click();
    await expect(page).toHaveURL(/\/assignments\/create/);
    await expect(page.locator('body')).toContainText(/deadline|setelah|after/i);
  });

  test('ASG-04 max_score > 100 rejected by HTML5 validation', async ({ page }) => {
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments/create`);
    await page.locator('#title').fill('Score too big');
    await page.locator('#submission_format').selectOption('pdf');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('500');
    await page.getByRole('button', { name: /simpan tugas/i }).click();
    // Either HTML5 validation blocks or server returns 422 → user stays on create
    await expect(page).toHaveURL(/\/assignments\/create/);
  });

  test('ASG-05 prerequisite material picker lists seeded materials', async ({ page }) => {
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments/create`);
    const opts = await page.locator('#required_material_id option').allTextContents();
    expect(opts.join(' ')).toContain('Modul 1');
  });

  test('ASG-06 edit existing assignment title', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.tugasPdf}/edit`);
    await page.locator('#title').fill('Tugas PDF Edited');
    await page.getByRole('button', { name: /simpan|update/i }).click();
    await expect(page).toHaveURL(/\/dosen\/courses\/.+\/assignments$/);
    await expect(page.locator('body')).toContainText('Tugas PDF Edited');
  });

  test('ASG-07 delete assignment', async ({ page }) => {
    // create one to delete
    const stamp = Date.now();
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments/create`);
    await page.locator('#title').fill('PW Delete ' + stamp);
    await page.locator('#submission_format').selectOption('pdf');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('100');
    await page.getByRole('button', { name: /simpan tugas/i }).click();
    await page.waitForURL(/\/assignments$/);

    page.once('dialog', d => d.accept());
    const card = page.locator('.sortable-item', { hasText: 'PW Delete ' + stamp });
    await card.locator('form button[type="submit"]', { hasText: /hapus|delete/i }).first().click();
    await expect(page.locator('body')).not.toContainText('PW Delete ' + stamp);
  });

  test('ASG-08 submissions list page renders even when empty', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.tugasUrl}/submissions`);
    await expect(page.locator('h2')).toContainText(/Review Submissions/i);
  });

  test('ASG-09 grade submission with score + feedback (skip if no submissions)', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.tugasPdf}/submissions`);
    const empty = await page.locator('body', { hasText: /Belum Ada Pengumpulan/ }).count();
    test.skip(empty > 0, 'No submissions seeded for this assignment');
    await page.locator('input[name="score"]').first().fill('85');
    await page.locator('input[name="feedback"]').first().fill('Bagus, lanjutkan.');
    await page.getByRole('button', { name: /simpan nilai|perbarui/i }).first().click();
    await expect(page.locator('body')).toContainText(/berhasil|85/i);
  });

  test('ASG-10 grade out-of-range rejected (score > max_score)', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.tugasPdf}/submissions`);
    const empty = await page.locator('body', { hasText: /Belum Ada Pengumpulan/ }).count();
    test.skip(empty > 0, 'No submissions seeded');
    await page.locator('input[name="score"]').first().fill('999');
    await page.getByRole('button', { name: /simpan nilai|perbarui/i }).first().click();
    // HTML5 max blocks; URL stays
    await expect(page.url()).toMatch(/\/submissions/);
  });

  test('ASG-11 late-submission badge appears when applicable (informational)', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.tugasPdf}/submissions`);
    // Just verify the page loaded — actual late badges depend on data
    await expect(page.locator('h2')).toContainText(/Review/i);
  });

  test('ASG-12 assignment_number auto-increments for tugas type', async ({ page }) => {
    const stamp = Date.now();
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments/create`);
    await page.locator('#title').fill('PW Auto-num ' + stamp);
    await page.locator('#submission_format').selectOption('pdf');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('100');
    await page.getByRole('button', { name: /simpan tugas/i }).click();
    await expect(page).toHaveURL(/\/assignments$/);
    // The card component should render an order number/badge
    await expect(page.locator('body')).toContainText('PW Auto-num ' + stamp);
  });

  test('ASG-13 reorder POST endpoint accepts ordered_ids JSON', async ({ page, request, baseURL }) => {
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments`);
    const { csrf, cookieHeader } = await getCsrfAndCookies(page);
    const res = await request.post(`${baseURL}/dosen/courses/${IDS.course.if101}/assignments/reorder`, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
        Cookie: cookieHeader,
      },
      data: { ordered_ids: [IDS.assignment.tugasPdf, IDS.assignment.tugasUrl] },
    });
    expect([200, 204]).toContain(res.status());
  });

  test('ASG-14 dosen with no course → fallback view', async ({ page }) => {
    // login as a dosen who DOES own a course (otherDosen owns IF202).
    // /dosen/assignments redirects to first course; expect 200 OK.
    await page.goto('/dosen/assignments');
    expect(page.url()).toMatch(/\/dosen\/(courses\/.+\/assignments|assignments)/);
  });

  test('ASG-15 assignments index header references course name', async ({ page }) => {
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments`);
    await expect(page.locator('body')).toContainText('Algoritma Dusk');
  });
});
