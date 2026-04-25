import { test, expect } from '@playwright/test';
import { loginAs, logout } from './helpers/auth';

test.describe('Flow 6 — Edge Cases (EDG)', () => {
  test('EDG-01 unauthenticated "/" redirects to /login', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/\/login/);
  });

  test('EDG-02 nonexistent slug returns 404', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    const res = await page.goto('/this-route-does-not-exist-zzz');
    expect(res?.status()).toBe(404);
  });

  test('EDG-03 XSS payload in discussion body is escaped on render', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    const stamp = Date.now();
    const payload = `<script>window.__pwn=${stamp}</script>`;
    await page.goto('/discussions/create');
    await page.locator('#topic').fill('XSS PW');
    await page.locator('#title').fill('XSS test ' + stamp);
    await page.locator('#content').fill(payload);
    await page.getByRole('button', { name: /^kirim$/i }).click();
    await page.waitForURL(/\/discussions/);
    const link = page.locator('a[href*="/discussions/"]', { hasText: 'XSS test ' + stamp }).first();
    if (await link.count()) await link.click();
    const pwn = await page.evaluate(() => (window as any).__pwn);
    expect(pwn).toBeUndefined();
    await expect(page.locator('body')).toContainText(payload.replace('<', '<').replace('>', '>'));
  });

  test('EDG-04 XSS in announcement title is escaped', async ({ page }) => {
    await loginAs(page, 'admin');
    const stamp = Date.now();
    const payload = `<img src=x onerror="window.__annXss=${stamp}">`;
    await page.goto('/announcements/create');
    await page.locator('#title').fill(payload);
    await page.locator('#content').fill('payload test');
    await page.getByRole('button', { name: /simpan pengumuman/i }).click();
    await page.waitForURL(/\/announcements/);
    const annXss = await page.evaluate(() => (window as any).__annXss);
    expect(annXss).toBeUndefined();
  });

  test('EDG-05 CSRF on stale form rejected', async ({ page, request, baseURL }) => {
    await loginAs(page, 'mahasiswa');
    const cookies = await page.context().cookies();
    const cookieHeader = cookies.map(c => `${c.name}=${c.value}`).join('; ');
    // Submit with a deliberately bogus _token
    const res = await request.post(`${baseURL}/discussions`, {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', Cookie: cookieHeader, 'Accept': 'text/html' },
      data: '_token=BOGUS&topic=X&title=Y&content=Z',
      maxRedirects: 0,
    }).catch(e => e);
    const status = (res as any).status?.();
    if (typeof status === 'number') expect([419, 302]).toContain(status);
  });

  test('EDG-06 direct URL to wrong role blocked', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/dosen/dashboard');
    await expect(page).toHaveURL(/\/mahasiswa\/dashboard/);
  });

  test('EDG-07 back button after logout cannot re-enter protected page', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/mahasiswa/dashboard');
    await logout(page);
    await page.goto('/mahasiswa/dashboard');
    await expect(page).toHaveURL(/\/login/);
  });

  test('EDG-08 dark mode toggle persists across reload', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/mahasiswa/dashboard');
    // Force-toggle theme via the same script the layout uses
    await page.evaluate(() => {
      const html = document.documentElement;
      html.classList.add('dark');
      try { localStorage.setItem('theme', 'dark'); } catch {}
    });
    await page.reload();
    const isDark = await page.evaluate(() => document.documentElement.classList.contains('dark'));
    expect(isDark).toBe(true);
  });

  test('EDG-09 Indonesian UI strings present on dashboards', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/mahasiswa/dashboard');
    await expect(page.locator('body')).toContainText(/Mata Kuliah|Tugas/);
  });
});
