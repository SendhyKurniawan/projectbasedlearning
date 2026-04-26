import { defineConfig, devices } from '@playwright/test';

const BASE_URL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:8000';

export default defineConfig({
  testDir: './tests/playwright',
  testMatch: /.*\.spec\.ts$/,
  globalSetup: './tests/playwright/global-setup.ts',
  fullyParallel: false,
  workers: 1,
  retries: process.env.CI ? 1 : 0,
  timeout: 90_000,
  expect: { timeout: 10_000 },
  webServer: {
    command: 'docker exec pjbl-app php artisan serve --host=0.0.0.0 --port=8000',
    url: BASE_URL,
    reuseExistingServer: true,
    timeout: 30_000,
  },
  reporter: [
    ['list'],
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
    ['json', { outputFile: 'playwright-report/results.json' }],
  ],
  outputDir: '.playwright-mcp/test-results',
  use: {
    baseURL: BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 15_000,
    navigationTimeout: 60_000,
    locale: 'id-ID',
    timezoneId: 'Asia/Jakarta',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
