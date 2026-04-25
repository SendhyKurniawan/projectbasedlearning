import type { Page } from '@playwright/test';
import { expect } from '@playwright/test';
import { USERS, DASHBOARD_URL, type Role } from '../fixtures';

/**
 * Log in via the real /login form and wait for the role-based redirect.
 * Uses the same selectors a real user would: #email and #password from
 * resources/views/auth/login.blade.php.
 */
export async function loginAs(page: Page, role: Role): Promise<void> {
  const user = USERS[role];
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  await page.locator('#email').fill(user.email);
  await page.locator('#password').fill(user.password);
  // Submit form directly — avoids racing the click/navigation pair on slow PHP-FPM.
  await page.locator('form[action*="/login"]').first().evaluate((f: HTMLFormElement) => f.submit());
  await page.waitForURL(/\/(admin|dosen|mahasiswa)\/dashboard/, { timeout: 30_000 });
  await expect(page).toHaveURL(new RegExp(DASHBOARD_URL[user.role] + '$'));
}

/** Hit the logout route. The app exposes POST /logout via the nav menu. */
export async function logout(page: Page): Promise<void> {
  // Most layouts render a form with a logout button; fall back to direct POST.
  const logoutButton = page.getByRole('button', { name: /log\s?out|keluar/i });
  if (await logoutButton.count()) {
    await Promise.all([
      page.waitForURL(/\/(login|$)/),
      logoutButton.first().click(),
    ]);
    return;
  }
  await page.request.post('/logout');
  await page.goto('/login');
}
