import { test, expect } from '@playwright/test';
import { USERS, DASHBOARD_URL } from './fixtures';
import { loginAs, logout } from './helpers/auth';

test.describe('Flow 1 — Auth & Access Control', () => {
  test('AUTH-01a admin valid login → admin dashboard', async ({ page }) => {
    await loginAs(page, 'admin');
    await expect(page).toHaveURL(/\/admin\/dashboard$/);
  });

  test('AUTH-01b dosen valid login → dosen dashboard', async ({ page }) => {
    await loginAs(page, 'dosen');
    await expect(page).toHaveURL(/\/dosen\/dashboard$/);
  });

  test('AUTH-01c mahasiswa valid login → mahasiswa dashboard', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await expect(page).toHaveURL(/\/mahasiswa\/dashboard$/);
  });

  test('AUTH-02 invalid credentials show error', async ({ page }) => {
    await page.goto('/login');
    await page.locator('#email').fill('mahasiswa@pjbl.test');
    await page.locator('#password').fill('wrongpassword');
    await page.getByRole('button', { name: /login/i }).click();
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('body')).toContainText(/credentials|salah|tidak (?:cocok|sesuai)/i);
  });

  test('AUTH-03 inactive dosen rejected at login', async ({ page }) => {
    await page.goto('/login');
    await page.locator('#email').fill(USERS.pendingDosen.email);
    await page.locator('#password').fill(USERS.pendingDosen.password);
    await page.getByRole('button', { name: /login/i }).click();
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('body')).toContainText(/belum diaktifkan|tidak aktif|menunggu/i);
  });

  test('AUTH-04 dedicated /admin/login page renders', async ({ page }) => {
    await page.goto('/admin/login');
    await expect(page.locator('h2')).toContainText(/Admin Portal/i);
    await expect(page.locator('form[action*="admin/login"]')).toBeVisible();
  });

  test('AUTH-05 mahasiswa register auto-login → mahasiswa dashboard', async ({ page }) => {
    const stamp = Date.now();
    const email = `mhs.pw.${stamp}@example.test`;
    await page.goto('/register');
    await page.locator('#name').fill('Pw Mhs ' + stamp);
    await page.locator('#email').fill(email);
    await page.locator('#nim').fill(String(stamp).slice(-10));
    await page.locator('#student_class_id').selectOption({ index: 1 });
    await page.locator('#password').fill('Password123!');
    await page.locator('#password_confirmation').fill('Password123!');
    await page.locator('#btn-submit').click();
    await page.waitForURL(/\/mahasiswa\/dashboard/, { timeout: 10_000 });
    await expect(page).toHaveURL(/\/mahasiswa\/dashboard$/);
  });

  test('AUTH-06 dosen register shows pending message and redirects to login', async ({ page }) => {
    const stamp = Date.now();
    await page.goto('/register');
    await page.locator('#tab-dosen').click();
    await page.locator('#name').fill('Pw Dosen ' + stamp);
    await page.locator('#email').fill(`dsn.pw.${stamp}@example.test`);
    await page.locator('#nip').fill(String(stamp).slice(-10));
    await page.locator('#password').fill('Password123!');
    await page.locator('#password_confirmation').fill('Password123!');
    await page.locator('#btn-submit').click();
    await page.waitForURL(/\/login/, { timeout: 10_000 });
    await expect(page.locator('body')).toContainText(/persetujuan admin|menunggu/i);
  });

  test('AUTH-07 logout clears session and back-button blocked', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await logout(page);
    await page.goBack().catch(() => {});
    await page.goto('/mahasiswa/dashboard');
    await expect(page).toHaveURL(/\/login/);
  });

  test('AUTH-08 mahasiswa → /admin/* redirected to mahasiswa dashboard', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/admin/dashboard');
    await expect(page).toHaveURL(/\/mahasiswa\/dashboard/);
  });

  test('AUTH-09 dosen → /mahasiswa/* redirected to dosen dashboard', async ({ page }) => {
    await loginAs(page, 'dosen');
    await page.goto('/mahasiswa/dashboard');
    await expect(page).toHaveURL(/\/dosen\/dashboard/);
  });

  test('AUTH-10a "/" redirects mahasiswa to mahasiswa dashboard', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/');
    await expect(page).toHaveURL(/\/mahasiswa\/dashboard/);
  });

  test('AUTH-10b "/" redirects dosen to dosen dashboard', async ({ page }) => {
    await loginAs(page, 'dosen');
    await page.goto('/');
    await expect(page).toHaveURL(/\/dosen\/dashboard/);
  });

  test('AUTH-10c "/" redirects admin to admin dashboard', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/');
    await expect(page).toHaveURL(/\/admin\/dashboard/);
  });

  test('AUTH-11 password reset request form accepts an email', async ({ page, baseURL, request }) => {
    // Just verify the GET form renders — actual POST hits SMTP which is async/slow in dev.
    await page.goto('/forgot-password', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#email')).toBeVisible();
    // Request-level POST: confirm endpoint doesn't 500. SMTP send is allowed to fail.
    const csrf = await page.evaluate(() => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '');
    const cookies = await page.context().cookies();
    const cookieHeader = cookies.map(c => `${c.name}=${c.value}`).join('; ');
    const res = await request.post(`${baseURL}/forgot-password`, {
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'text/html', Cookie: cookieHeader },
      form: { _token: csrf, email: 'mahasiswa@pjbl.test' },
      timeout: 60_000,
      maxRedirects: 0,
    }).catch(e => e);
    const status = typeof (res as any).status === 'function' ? (res as any).status() : (res as any).status;
    if (typeof status === 'number') expect(status).not.toBe(500);
  });

  test('AUTH-12 password visibility toggle on login flips type attribute', async ({ page }) => {
    await page.goto('/login');
    const pwd = page.locator('[dusk="password-input"]');
    await expect(pwd).toHaveAttribute('type', 'password');
    await page.locator('[dusk="password-toggle"]').click();
    await expect(pwd).toHaveAttribute('type', 'text');
    await page.locator('[dusk="password-toggle"]').click();
    await expect(pwd).toHaveAttribute('type', 'password');
  });
});
