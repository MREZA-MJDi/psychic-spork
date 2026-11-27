import { chromium } from 'playwright';
import assert from 'node:assert/strict';
import { mkdir } from 'node:fs/promises';

const base = process.env.UI_BASE_URL || 'http://127.0.0.1:8000';
await mkdir('artifacts/ui-smoke', { recursive: true });
const browser = await chromium.launch();
try {
    for (const width of [390, 1440]) {
        const page = await browser.newPage({ viewport: { width, height: 900 }, reducedMotion: 'reduce' });
        await page.goto(base + '/login');
        await page.locator('input[name="phone"]').fill('09123456789');
        await page.locator('input[name="password"]').fill('password');
        await page.locator('form.auth-form button[type="submit"]').click();
        await page.waitForURL(url => /^\/admin\/?$/.test(url.pathname));
        await page.goto(base + '/admin/wholesale');
        const workspace = page.locator('.wholesale-admin');
        await workspace.waitFor();
        assert.equal(await workspace.locator('[name="minimum_order_amount"]').count(), 0);
        await workspace.locator('[name="q"]').fill('Wholesale QA Alpha');
        await Promise.all([
            page.waitForURL(url => url.searchParams.get('q') === 'Wholesale QA Alpha'),
            workspace.getByRole('button', { name: 'جستجو', exact: true }).click(),
        ]);
        assert.equal(await workspace.locator('tbody tr').count(), 1);
        assert.match(await workspace.locator('tbody').innerText(), /Wholesale QA Alpha/);
        await page.screenshot({path: 'artifacts/ui-smoke/' + width + '-wholesale-admin-list.png', fullPage: true});
        await workspace.getByRole('link', { name: 'ویرایش شرایط' }).click();
        await page.waitForURL(url => url.searchParams.has('edit'));
        const editor = workspace.locator('#wholesale-editor');
        await editor.waitFor();
        assert.equal(await editor.locator('form.wholesale-admin__form').count(), 1);
        await editor.locator('[name="minimum_order_amount"]').fill('۴٬۰۰۰٬۰۰۰');
        await editor.locator('[name="minimum_order_quantity"]').fill('8');
        await editor.locator('[name="note"]').fill('Browser QA terms');
        await page.screenshot({path: 'artifacts/ui-smoke/' + width + '-wholesale-admin-edit.png', fullPage: true});
        await Promise.all([
            page.waitForNavigation(),
            editor.getByRole('button', { name: 'ذخیره شرایط', exact: true }).click(),
        ]);
        await page.locator('.alert.success').waitFor();
        assert.equal(await editor.locator('[name="minimum_order_quantity"]').inputValue(), '8');
        assert.equal(await editor.locator('[name="note"]').inputValue(), 'Browser QA terms');
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2));
        await editor.getByRole('link', { name: 'بستن', exact: true }).click();
        await page.waitForURL(url => !url.searchParams.has('edit'));
        assert.equal(await workspace.locator('[name="minimum_order_amount"]').count(), 0);
        await page.close();
    }
} finally { await browser.close(); }
console.log('Wholesale workspace filter, single editor, save and responsive layout verified.');
