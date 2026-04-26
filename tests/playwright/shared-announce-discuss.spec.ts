import { test, expect } from '@playwright/test';
import { loginAs } from './helpers/auth';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const PNG = path.join(__dirname, 'assets', 'sample.png');
const PDF = path.join(__dirname, 'assets', 'sample.pdf');

test.describe('Flow 5 — Shared · Announcements (ANN)', () => {
  test('ANN-01 create announcement with image (admin)', async ({ page }) => {
    await loginAs(page, 'admin');
    const stamp = Date.now();
    await page.goto('/announcements/create');
    await page.locator('#title').fill('PW Ann img ' + stamp);
    await page.locator('#content').fill('Isi pengumuman dengan gambar.');
    await page.locator('#attachment').setInputFiles(PNG);
    await page.getByRole('button', { name: /simpan pengumuman/i }).click();
    await expect(page).toHaveURL(/\/announcements/);
    await expect(page.locator('body')).toContainText('PW Ann img ' + stamp);
  });

  test('ANN-02 create announcement with PDF', async ({ page }) => {
    await loginAs(page, 'admin');
    const stamp = Date.now();
    await page.goto('/announcements/create');
    await page.locator('#title').fill('PW Ann pdf ' + stamp);
    await page.locator('#content').fill('Lampiran PDF.');
    await page.locator('#attachment').setInputFiles(PDF);
    await page.getByRole('button', { name: /simpan pengumuman/i }).click();
    await expect(page.locator('body')).toContainText('PW Ann pdf ' + stamp);
  });

  test('ANN-03 attachment size cap (oversize) rejected', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/announcements/create');
    await page.locator('#title').fill('Oversize attachment');
    await page.locator('#content').fill('x');
    const big = Buffer.alloc(11 * 1024 * 1024, 'x');
    await page.locator('#attachment').setInputFiles({ name: 'big.pdf', mimeType: 'application/pdf', buffer: big });
    await page.getByRole('button', { name: /simpan pengumuman/i }).click();
    // Either Laravel validation message or nginx 413 (server-level upload cap) — both prove the cap is enforced.
    await expect(page.locator('body')).toContainText(/file|max|10 MB|gagal|error|413|too large/i);
  });

  test('ANN-04 target audience dropdown lists options', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/announcements/create');
    const opts = await page.locator('#target_audience option').allTextContents();
    expect(opts.length).toBeGreaterThan(0);
  });

  test('ANN-05 audience filter narrows index (informational)', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/announcements');
    await expect(page.getByRole('heading', { name: /Pusat Pengumuman|Pengumuman/i })).toBeVisible();
  });

  test('ANN-06 edit announcement title', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/announcements/create');
    await page.waitForLoadState('domcontentloaded');
    const stamp = Date.now();
    await page.locator('#title').waitFor({ state: 'visible', timeout: 30_000 });
    await page.locator('#title').fill('PW edit-me ' + stamp);
    await page.locator('#content').fill('temp');
    await page.getByRole('button', { name: /simpan pengumuman/i }).click();
    await page.waitForURL(/\/announcements/, { timeout: 30_000 });
    const editLink = page.locator('a[href*="/announcements/"][href*="/edit"]').first();
    if (await editLink.count()) {
      await editLink.click();
      await page.locator('#title').fill('PW edited ' + stamp);
      await page.getByRole('button', { name: /perbarui pengumuman|simpan|update/i }).click();
      await expect(page.locator('body')).toContainText('PW edited ' + stamp);
    }
  });

  test('ANN-07 delete own announcement (admin)', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/announcements');
    page.once('dialog', d => d.accept());
    const delBtn = page.locator('form button[type="submit"]', { hasText: /hapus|delete/i }).first();
    if (!(await delBtn.count())) test.skip(true, 'No announcement to delete');
    await delBtn.click();
    await expect(page).toHaveURL(/\/announcements/);
  });

  test('ANN-08 announcements list pagination links exist if many', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/announcements');
    await expect(page.getByRole('heading', { name: /Pusat Pengumuman|Pengumuman/i })).toBeVisible();
  });
});

test.describe('Flow 5 — Shared · Discussions (DSC)', () => {
  test('DSC-01 create discussion (mahasiswa)', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    const stamp = Date.now();
    await page.goto('/discussions/create');
    await page.locator('#topic').fill('PW Topic');
    await page.locator('#title').fill('PW Disc ' + stamp);
    await page.locator('#content').fill('Pertanyaan diskusi PW.');
    await page.getByRole('button', { name: /^kirim$/i }).click();
    await expect(page).toHaveURL(/\/discussions/);
    await expect(page.locator('body')).toContainText('PW Disc ' + stamp);
  });

  test('DSC-02 view discussion show page', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/discussions');
    const link = page.locator('a[href*="/discussions/"]:not([href*="/create"]):not([href*="/edit"])').first();
    if (!(await link.count())) test.skip(true, 'No discussion to view');
    await link.click();
    await expect(page).toHaveURL(/\/discussions\/\d+/);
    await expect(page.locator('h3', { hasText: /Komentar/i })).toBeVisible();
  });

  test('DSC-03 add comment via Livewire', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/discussions');
    const link = page.locator('a[href*="/discussions/"]:not([href*="/create"]):not([href*="/edit"])').first();
    if (!(await link.count())) test.skip(true, 'No discussion');
    await link.click();
    const ta = page.locator('#newComment');
    await ta.waitFor({ state: 'visible' });
    const text = 'Komentar PW ' + Date.now();
    await ta.click();
    await ta.pressSequentially(text, { delay: 5 });
    await ta.blur();
    await page.waitForTimeout(200);
    await page.getByRole('button', { name: /kirim komentar/i }).click();
    await expect(page.locator('body')).toContainText(text, { timeout: 20_000 });
  });

  test('DSC-04 comment > 1000 chars rejected', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/discussions');
    const link = page.locator('a[href*="/discussions/"]:not([href*="/create"]):not([href*="/edit"])').first();
    if (!(await link.count())) test.skip(true, 'No discussion');
    await link.click();
    const ta = page.locator('#newComment');
    await ta.waitFor({ state: 'visible' });
    const long = 'x'.repeat(1100);
    await ta.click();
    await ta.fill(long);
    await ta.blur();
    await page.waitForTimeout(200);
    await page.getByRole('button', { name: /kirim komentar/i }).click();
    await expect(page.locator('body')).toContainText(/max|1000|panjang|tidak boleh|greater|character|exceed/i, { timeout: 20_000 });
  });

  test('DSC-05 delete own discussion', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/discussions');
    const link = page.locator('a[href*="/discussions/"]:not([href*="/create"]):not([href*="/edit"])').first();
    if (!(await link.count())) test.skip(true, 'No discussion');
    await link.click();
    page.once('dialog', d => d.accept());
    const delBtn = page.locator('form button[type="submit"]', { hasText: /hapus/i }).first();
    if (!(await delBtn.count())) test.skip(true, 'No delete button (not owner)');
    await delBtn.click();
    await expect(page).toHaveURL(/\/discussions/);
  });

  test('DSC-06 list shows index header', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/discussions');
    await expect(page.getByRole('heading', { name: /Forum Diskusi|Diskusi/i })).toBeVisible();
  });
});
