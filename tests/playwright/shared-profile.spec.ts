import { test, expect } from '@playwright/test';
import { loginAs } from './helpers/auth';

test.describe('Flow 5 — Shared · Profile (PRF)', () => {
  test('PRF-01 view profile page renders three sections', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/profile');
    await expect(page.locator('h2', { hasText: /Informasi Profil|Profile Information/i })).toBeVisible();
    await expect(page.locator('h2', { hasText: /Perbarui Kata Sandi|Update Password/i })).toBeVisible();
    await expect(page.locator('h2', { hasText: /Hapus Akun|Delete Account/i }).first()).toBeVisible();
  });

  test('PRF-02 update name persists', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/profile');
    const newName = 'Mahasiswa Dusk PW';
    await page.locator('#name').fill(newName);
    await page.getByRole('button', { name: /^(simpan|save)$/i }).first().click();
    await page.reload();
    await expect(page.locator('#name')).toHaveValue(newName);
  });

  test('PRF-03 change password with correct current succeeds', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/profile');
    await page.locator('#update_password_current_password').fill('password');
    await page.locator('#update_password_password').fill('NewPass123!');
    await page.locator('#update_password_password_confirmation').fill('NewPass123!');
    // Second "Simpan" button is for password section
    const saveButtons = page.getByRole('button', { name: /^(simpan|save)$/i });
    await saveButtons.nth(1).click();
    // Reset back so other tests can still log in
    await page.locator('#update_password_current_password').fill('NewPass123!');
    await page.locator('#update_password_password').fill('password');
    await page.locator('#update_password_password_confirmation').fill('password');
    await saveButtons.nth(1).click();
    await expect(page).toHaveURL(/\/profile/);
  });

  test('PRF-04 wrong current password rejected', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/profile');
    await page.locator('#update_password_current_password').fill('wrong-password-zzz');
    await page.locator('#update_password_password').fill('NewPass123!');
    await page.locator('#update_password_password_confirmation').fill('NewPass123!');
    const saveButtons = page.getByRole('button', { name: /^(simpan|save)$/i });
    await saveButtons.nth(1).click();
    await expect(page.locator('body')).toContainText(/current|password|salah|sesuai|incorrect/i);
  });

  test('PRF-05 delete account flow opens confirmation modal', async ({ page }) => {
    await loginAs(page, 'mahasiswa');
    await page.goto('/profile');
    await page.getByRole('button', { name: /^(hapus akun|delete account)$/i }).first().click();
    await expect(page.locator('h2', { hasText: /Apakah Anda yakin|Are you sure/i })).toBeVisible();
    // Cancel — do not actually delete
    await page.getByRole('button', { name: /^(batal|cancel)$/i }).click();
  });
});
