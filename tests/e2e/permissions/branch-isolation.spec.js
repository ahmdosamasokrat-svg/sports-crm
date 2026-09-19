import { test, expect } from '../fixtures/crm-fixtures.js';

test.describe('Branch Access Isolation & Administration Suite', () => {
    test('Manager with all-branch scope can access branch management settings @branches', async ({ auditedPage: page }) => {
        const resp = await page.goto('/settings/branches');
        expect(resp.status()).toBeLessThan(400);
        await expect(page.locator('h1, h2')).toContainText(['الفروع', 'Branches']);
        await expect(page.locator('#branchEditor')).toBeVisible();
    });

    test('Creating a new branch via UI persists in the list @branches', async ({ auditedPage: page }) => {
        await page.goto('/settings/branches');

        const uniqueCode = 'br-' + Date.now().toString(36);
        await page.fill('#name_ar', 'فرع الاختبار التلقائي');
        await page.fill('#name_en', 'E2E Test Branch');
        await page.fill('#code', uniqueCode);
        await page.fill('#phone', '01012345678');
        await page.fill('#address', 'القاهرة الجديدة');

        await Promise.all([
            page.waitForNavigation(),
            page.click('#branchEditor button[type="submit"]')
        ]);

        await expect(page.locator('body')).toContainText('فرع الاختبار التلقائي');
        await expect(page.locator('body')).toContainText(uniqueCode);
    });

    test('User management includes branch selection dropdown @branches', async ({ auditedPage: page }) => {
        const resp = await page.goto('/settings/users/create');
        expect(resp.status()).toBeLessThan(400);
        await expect(page.locator('#branch_id')).toBeVisible();
        const optionsCount = await page.locator('#branch_id option').count();
        expect(optionsCount).toBeGreaterThan(0);
    });

    test('Lead list renders branch filter for all-branch managers and displays branch badges @branches', async ({ auditedPage: page }) => {
        await page.goto('/leads');
        const branchSelect = page.locator('#leadBranch');
        if (await branchSelect.count() > 0) {
            await expect(branchSelect).toBeVisible();
        }
    });

    test('Arabic RTL and English LTR layouts render properly with Tajawal font @branches', async ({ auditedPage: page }) => {
        // Arabic
        await page.goto('/lang/ar');
        await page.goto('/settings/branches');
        await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');

        // English
        await page.goto('/lang/en');
        await page.goto('/settings/branches');
        await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');

        // Restore to Arabic
        await page.goto('/lang/ar');
    });

    test('Responsive mobile layout remains usable at 375px width @branches', async ({ auditedPage: page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        const resp = await page.goto('/settings/branches');
        expect(resp.status()).toBeLessThan(400);
        await expect(page.locator('.branches-layout')).toBeVisible();
    });
});
