import { test, expect } from '../fixtures/crm-fixtures.js';

test('All stages can be shown and current-stage visibility restored @stage @profile', async ({ auditedPage: page }) => {
    await page.goto('/leads');

    const profilePath = await page.locator('a[href]').evaluateAll(anchors =>
        anchors
            .map(anchor => new URL(anchor.href).pathname)
            .find(path => /^\/leads\/\d+$/.test(path)) ?? null
    );
    test.skip(!profilePath, 'No accessible lead is available to open');

    await page.goto(profilePath);
    const stageTab = page.locator('[data-tab-key="stage_data"]');
    test.skip(await stageTab.count() === 0, 'Stage questions are unavailable for this lead');
    await stageTab.click();

    const sections = page.locator('#lead-stage-panel .stage-section-wrapper');
    const sectionCount = await sections.count();
    test.skip(sectionCount < 2, 'Fewer than two stages are configured');

    const checkbox = page.locator('#toggleAllStagesCheckbox');
    const modeLabel = page.locator('#stageViewModeLabel');
    const visibleSections = page.locator('#lead-stage-panel .stage-section-wrapper:visible');
    const currentLabel = await modeLabel.getAttribute('data-current-label');
    const allLabel = await modeLabel.getAttribute('data-all-label');

    await expect(checkbox).not.toBeChecked();
    await expect(visibleSections).toHaveCount(1);
    await expect(page.locator('#lead-stage-panel .stage-section-wrapper[data-is-current="1"]:visible')).toHaveCount(1);
    await expect(modeLabel).toHaveText(currentLabel);

    await checkbox.check();
    await expect(visibleSections).toHaveCount(sectionCount);
    await expect(modeLabel).toHaveText(allLabel);

    await checkbox.uncheck();
    await expect(visibleSections).toHaveCount(1);
    await expect(page.locator('#lead-stage-panel .stage-section-wrapper[data-is-current="1"]:visible')).toHaveCount(1);
    await expect(modeLabel).toHaveText(currentLabel);
});
