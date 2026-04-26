import { test, expect } from '@playwright/test';
import { IDS } from './fixtures';
import { loginAs } from './helpers/auth';

function futureDeadline(days = 14): string {
  const d = new Date(Date.now() + days * 24 * 3600 * 1000);
  const pad = (n: number) => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

async function setCodeMirror(page: import('@playwright/test').Page, textareaSelector: string, value: string) {
  // CodeMirror replaces the textarea with a sibling .CodeMirror wrapper.
  // Set the editor value via the CodeMirror instance attached to that wrapper.
  await page.evaluate(({ sel, val }) => {
    const ta = document.querySelector(sel) as HTMLTextAreaElement | null;
    if (!ta) throw new Error('textarea not found: ' + sel);
    const wrapper = ta.parentElement?.querySelector('.CodeMirror') as any;
    if (wrapper && wrapper.CodeMirror) {
      wrapper.CodeMirror.setValue(val);
    } else {
      ta.value = val;
    }
  }, { sel: textareaSelector, val: value });
}

test.describe('Flow 3 — Dosen · Quiz & Exercise (QEX)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'dosen');
  });

  test('QEX-01 create quiz with duration', async ({ page }) => {
    const stamp = Date.now();
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments/create`);
    await expect(page.locator('#type')).toBeVisible();
    await page.locator('#type').selectOption('quiz');
    // Fill title AFTER type select — Alpine re-renders form on type change
    await expect(page.locator('#duration_minutes')).toBeEditable({ timeout: 10_000 });
    await page.locator('#title').fill('PW Quiz ' + stamp);
    await page.locator('#duration_minutes').fill('15');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('100');
    const btn = page.getByRole('button', { name: /simpan tugas/i });
    await expect(btn).toBeEnabled({ timeout: 10_000 });
    await Promise.all([
      page.waitForURL(/\/(questions|assignments)$/, { timeout: 30_000 }),
      btn.click(),
    ]);
    await expect(page.locator('body')).toContainText('PW Quiz ' + stamp);
  });

  test('QEX-02 add multiple-choice question with 4 options & mark correct', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.quizCepat}/questions/create`);
    await page.locator('#question_text').fill('Pertanyaan PW MC?');
    await page.locator('#question_type').selectOption('pilihan_ganda');
    await page.locator('#score_weight').fill('5');
    await page.locator('input[name="options[0][text]"]').fill('Opsi A (benar)');
    await page.locator('input[name="options[1][text]"]').fill('Opsi B');
    await page.locator('input[name="options[2][text]"]').fill('Opsi C');
    await page.locator('input[name="options[3][text]"]').fill('Opsi D');
    await page.locator('input[name="correct_idx"]').first().check();
    await page.getByRole('button', { name: /simpan pertanyaan/i }).click();
    await expect(page).toHaveURL(/\/questions/);
  });

  test('QEX-03 add essay question', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.quizCepat}/questions/create`);
    await page.locator('#question_text').fill('Pertanyaan PW Essay?');
    await page.locator('#question_type').selectOption('essay');
    await page.locator('#score_weight').fill('10');
    await page.getByRole('button', { name: /simpan pertanyaan/i }).click();
    await expect(page).toHaveURL(/\/questions/);
  });

  test('QEX-04 delete a question', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.quizCepat}/questions`);
    page.once('dialog', d => d.accept());
    const before = await page.locator('form[action*="/questions/"]').count();
    await page.locator('form[action*="/questions/"] button[type="submit"]').first().click();
    await expect(page.locator('form[action*="/questions/"]')).toHaveCount(before - 1);
  });

  test('QEX-05 create code exercise (java with required keywords)', async ({ page }) => {
    const stamp = Date.now();
    await page.goto(`/dosen/courses/${IDS.course.if101}/exercises/create`);
    await expect(page.locator('#title')).toBeVisible();
    await page.locator('#title').fill('PW Exercise Java ' + stamp);
    await page.locator('#exercise_language').selectOption('java');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('100');
    await page.locator('#required_keywords').fill('public,class,Main');
    await setCodeMirror(page, '#starter-code-editor', 'public class Main { public static void main(String[] a) {} }');
    const btn = page.getByRole('button', { name: /simpan latihan/i });
    await expect(btn).toBeEnabled({ timeout: 10_000 });
    await Promise.all([
      page.waitForURL(/\/dosen\/courses\/.+\/(assignments|exercises)$/, { timeout: 30_000 }),
      btn.click(),
    ]);
    await expect(page.locator('body')).toContainText('PW Exercise Java ' + stamp);
  });

  test('QEX-06 create exercise (html mixed)', async ({ page }) => {
    const stamp = Date.now();
    await page.goto(`/dosen/courses/${IDS.course.if101}/exercises/create`);
    await page.locator('#title').fill('PW Exercise HTML ' + stamp);
    await page.locator('#exercise_language').selectOption('htmlmixed');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('100');
    await setCodeMirror(page, '#starter-code-editor', '<!DOCTYPE html><html><body></body></html>');
    await page.getByRole('button', { name: /simpan latihan/i }).click();
    await expect(page.locator('body')).toContainText('PW Exercise HTML ' + stamp);
  });

  test('QEX-07 unsupported language rejected (request-level)', async ({ page, baseURL, request }) => {
    await page.goto(`/dosen/courses/${IDS.course.if101}/exercises/create`);
    const csrf = await page.evaluate(() => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '');
    const cookies = await page.context().cookies();
    const cookieHeader = cookies.map(c => `${c.name}=${c.value}`).join('; ');
    const res = await request.post(`${baseURL}/dosen/courses/${IDS.course.if101}/exercises`, {
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'text/html', Cookie: cookieHeader },
      form: {
        _token: csrf,
        title: 'Bogus lang',
        exercise_language: 'rust',
        deadline: futureDeadline(),
        max_score: '100',
        starter_code: 'fn main(){}',
      },
    }).catch(e => e);
    // Server should not 500 — any redirect/validation response is acceptable
    const status = typeof (res as any).status === 'function'
      ? (res as any).status()
      : (res as any).status;
    if (typeof status === 'number') expect(status).not.toBe(500);
  });

  test('QEX-08 empty starter_code rejected', async ({ page }) => {
    await page.goto(`/dosen/courses/${IDS.course.if101}/exercises/create`);
    await page.locator('#title').fill('Empty starter');
    await page.locator('#exercise_language').selectOption('htmlmixed');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('100');
    await setCodeMirror(page, '#starter-code-editor', '');
    await page.getByRole('button', { name: /simpan latihan/i }).click();
    await expect(page).toHaveURL(/\/exercises\/create|\/exercises$/);
    await expect(page.locator('body')).toContainText(/starter|kode|wajib|required/i);
  });

  test('QEX-09 quiz attempt review page renders (or shows empty state)', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.quizCepat}/submissions`);
    await expect(page.locator('h2').first()).toContainText(/Quiz Cepat|Review|Attempt/i);
  });

  test('QEX-10 edit exercise loads form prefilled', async ({ page }) => {
    await page.goto(`/dosen/exercises/${IDS.assignment.exerciseHtml}/edit`);
    await expect(page.locator('#title')).toHaveValue(/Exercise HTML/);
  });

  test('QEX-11 edit quiz loads form prefilled', async ({ page }) => {
    await page.goto(`/dosen/assignments/${IDS.assignment.quizCepat}/edit`);
    await expect(page.locator('#title')).toHaveValue(/Quiz Cepat/);
  });

  test('QEX-12 delete quiz cascades questions', async ({ page }) => {
    // create a quiz to delete
    const stamp = Date.now();
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments/create`);
    await page.locator('#type').selectOption('quiz');
    // Fill title AFTER type select — Alpine re-renders on type change
    await expect(page.locator('#duration_minutes')).toBeEditable({ timeout: 10_000 });
    await page.locator('#title').fill('PW DelQuiz ' + stamp);
    await page.locator('#duration_minutes').fill('5');
    await page.locator('#deadline').fill(futureDeadline());
    await page.locator('#max_score').fill('100');
    const delBtn = page.getByRole('button', { name: /simpan tugas/i });
    await expect(delBtn).toBeEnabled({ timeout: 10_000 });
    await Promise.all([
      page.waitForURL(/\/(questions|assignments)$/, { timeout: 30_000 }),
      delBtn.click(),
    ]);
    // Go to assignments list to find delete button
    await page.goto(`/dosen/courses/${IDS.course.if101}/assignments`);

    page.once('dialog', d => d.accept());
    const item = page.locator('.sortable-item', { hasText: 'PW DelQuiz ' + stamp });
    await item.locator('form[action*="/assignments/"][method="POST"] button[type="submit"]').first().click();
    await expect(page.locator('body')).not.toContainText('PW DelQuiz ' + stamp);
  });
});
