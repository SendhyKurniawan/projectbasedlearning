import { test, expect } from '@playwright/test';
import { USERS } from './fixtures';
import { loginAs } from './helpers/auth';

test.describe('Flow 2 — Admin · Users (USR)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'admin');
  });

  test('USR-01 create user (mahasiswa) succeeds', async ({ page }) => {
    const stamp = Date.now();
    const email = `pw.usr.${stamp}@example.test`;
    await page.goto('/admin/users/create');
    await page.locator('#name').fill('PW Created ' + stamp);
    await page.locator('#email').fill(email);
    await page.locator('#role').selectOption('mahasiswa');
    await page.locator('#nim').fill(String(stamp).slice(-10));
    await page.locator('#student_class_id').selectOption({ index: 1 });
    await page.locator('#password').fill('Password123!');
    await page.locator('#password_confirmation').fill('Password123!');
    await page.getByRole('button', { name: /create user/i }).click();
    await expect(page).toHaveURL(/\/admin\/users(\?|$)/);
    await expect(page.locator('body')).toContainText(email);
  });

  test('USR-02 toggle active button flips status', async ({ page }) => {
    await page.goto('/admin/users');
    const row = page.locator('tr', { hasText: USERS.pendingDosen.email });
    await row.locator('button[type="submit"]', { hasText: /aktifkan|nonaktifkan/i }).first().click();
    await expect(page.locator('body')).toContainText(/berhasil (di)?aktif/i);
  });

  test('USR-03 bulk destroy removes selected users', async ({ page }) => {
    await page.goto('/admin/users');
    const checkbox = page.locator(`input.user-checkbox[value="${USERS.otherDosen.id}"]`);
    await checkbox.check();
    page.once('dialog', d => d.accept());
    await page.getByRole('button', { name: /hapus terpilih/i }).click();
    await expect(page).toHaveURL(/\/admin\/users(\?|$)/);
    await expect(page.locator('tbody')).not.toContainText(USERS.otherDosen.email);
  });

  test('USR-04 search filters by email', async ({ page }) => {
    await page.goto('/admin/users');
    await page.locator('input[name="search"]').fill('mahasiswa@pjbl.test');
    await page.locator('form').filter({ has: page.locator('input[name="search"]') }).locator('button[type="submit"]').click();
    await expect(page.locator('tbody')).toContainText('mahasiswa@pjbl.test');
    await expect(page.locator('tbody tr')).toHaveCount(1);
  });

  test('USR-05 required-field validation on create', async ({ page }) => {
    await page.goto('/admin/users/create');
    await page.getByRole('button', { name: /create user/i }).click();
    // HTML5 required prevents submit; URL stays put
    await expect(page).toHaveURL(/\/admin\/users\/create/);
  });

  test('USR-06 duplicate email rejected', async ({ page }) => {
    await page.goto('/admin/users/create');
    await page.locator('#name').fill('Dup Email');
    await page.locator('#email').fill('mahasiswa@pjbl.test');
    await page.locator('#role').selectOption('mahasiswa');
    await page.locator('#nim').fill('99999' + String(Date.now()).slice(-5));
    await page.locator('#student_class_id').selectOption({ index: 1 });
    await page.locator('#password').fill('Password123!');
    await page.locator('#password_confirmation').fill('Password123!');
    await page.getByRole('button', { name: /create user/i }).click();
    await expect(page).toHaveURL(/\/admin\/users\/create/);
    await expect(page.locator('body')).toContainText(/email/i);
  });

  test('USR-07 duplicate NIM rejected', async ({ page }) => {
    await page.goto('/admin/users/create');
    const stamp = Date.now();
    await page.locator('#name').fill('Dup NIM ' + stamp);
    await page.locator('#email').fill(`dup.nim.${stamp}@example.test`);
    await page.locator('#role').selectOption('mahasiswa');
    await page.locator('#nim').fill('20240001'); // existing seeded NIM
    await page.locator('#student_class_id').selectOption({ index: 1 });
    await page.locator('#password').fill('Password123!');
    await page.locator('#password_confirmation').fill('Password123!');
    await page.getByRole('button', { name: /create user/i }).click();
    await expect(page).toHaveURL(/\/admin\/users\/create/);
    await expect(page.locator('body')).toContainText(/nim|sudah/i);
  });

  test('USR-08 role dropdown lists admin/dosen/mahasiswa', async ({ page }) => {
    await page.goto('/admin/users/create');
    const opts = await page.locator('#role option').allTextContents();
    expect(opts.join(' ').toLowerCase()).toMatch(/mahasiswa/);
    expect(opts.join(' ').toLowerCase()).toMatch(/dosen/);
    expect(opts.join(' ').toLowerCase()).toMatch(/admin/);
  });

  test('USR-09 role filter narrows table to dosen only', async ({ page }) => {
    await page.goto('/admin/users');
    await page.locator('select[name="role"]').selectOption('dosen');
    await page.locator('form').filter({ has: page.locator('select[name="role"]') }).locator('button[type="submit"]').click();
    const roleCells = page.locator('tbody tr td:nth-child(6)');
    const texts = (await roleCells.allTextContents()).map(t => t.trim().toLowerCase());
    for (const t of texts) expect(t).toMatch(/dosen/);
  });

  test('USR-10 status filter inactive shows only inactive users', async ({ page }) => {
    await page.goto('/admin/users?status=inactive');
    const statusCells = page.locator('tbody tr td:nth-child(7)');
    const texts = (await statusCells.allTextContents()).map(t => t.trim().toLowerCase());
    for (const t of texts) expect(t).toMatch(/menunggu|tidak/);
  });
});
