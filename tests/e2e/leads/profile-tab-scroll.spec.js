import { test, expect } from '../fixtures/crm-fixtures.js';

test('Profile tabs expose horizontal scrolling on narrow screens @profile @responsive', async ({ auditedPage: page }) => {
    await page.setViewportSize({ width: 320, height: 900 });
    await page.goto('/leads');

    const profilePath = await page.locator('a[href]').evaluateAll(anchors =>
        anchors
            .map(anchor => new URL(anchor.href).pathname)
            .find(path => /^\/leads\/\d+$/.test(path)) ?? null
    );
    test.skip(!profilePath, 'No accessible lead is available to open');
    await page.goto(profilePath);

    const nav = page.locator('#profileTabsNav');
    const metrics = await nav.evaluate(element => ({
        clientWidth: element.clientWidth,
        scrollWidth: element.scrollWidth,
        scrollbarHeight: getComputedStyle(element, '::-webkit-scrollbar').height,
    }));

    expect(metrics.scrollWidth).toBeGreaterThan(metrics.clientWidth);
    expect(metrics.scrollbarHeight).toBe('8px');

    await nav.evaluate(element => {
        element.scrollLeft = getComputedStyle(element).direction === 'rtl'
            ? -element.scrollWidth
            : element.scrollWidth;
    });
    await expect.poll(() => nav.evaluate(element => Math.abs(element.scrollLeft))).toBeGreaterThan(0);
});
