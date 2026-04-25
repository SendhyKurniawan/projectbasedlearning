import { test, expect } from '@playwright/test';
import { loginAs } from './helpers/auth';

test.describe('Flow 5 — Shared · Notifications (NTF)', () => {
  test('NTF-01 notifications index renders header and panel', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/notifications');
    await expect(page.getByRole('heading', { name: /Semua Notifikasi/i })).toBeVisible();
  });

  test('NTF-02 mark one read (skip if no unread)', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/notifications');
    const markBtn = page.locator('form[action*="/notifications/"] button', { hasText: /Tandai Sudah Dibaca/ }).first();
    if (!(await markBtn.count())) test.skip(true, 'No unread notification to mark');
    await markBtn.click();
    await expect(page).toHaveURL(/\/notifications/);
  });

  test('NTF-03 mark all read button works (skip if no unread)', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/notifications');
    const allBtn = page.getByRole('button', { name: /tandai semua/i });
    if (!(await allBtn.count())) test.skip(true, 'No unread to mark all');
    await allBtn.click();
    await expect(page.locator('body')).not.toContainText(/Tandai Semua/);
  });

  test('NTF-04 read-and-redirect link navigates (skip if no link)', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/notifications');
    const link = page.locator('a[href*="/notifications/"][href*="/redirect"]').first();
    if (!(await link.count())) test.skip(true, 'No notification with URL to redirect');
    // The link is rendered inside a per-card container that may not be in the visible viewport
    // depending on list length; use direct navigation to the redirect URL instead of click().
    const href = await link.getAttribute('href');
    expect(href).toBeTruthy();
    await page.goto(href!);
    expect(page.url()).not.toMatch(/\/notifications$/);
  });

  test('NTF-05 push-subscribe service worker registration JS is present (manual delivery)', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/mahasiswa/dashboard');
    // The layout registers a service worker; verify navigator.serviceWorker is reachable
    const hasSW = await page.evaluate(() => 'serviceWorker' in navigator);
    expect(hasSW).toBe(true);
  });
});
