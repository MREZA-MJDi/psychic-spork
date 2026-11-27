import { chromium } from 'playwright';
import assert from 'node:assert/strict';

const base = process.env.UI_BASE_URL || 'http://127.0.0.1:8000';
const browser = await chromium.launch();
try {
    for (const width of [390, 1440]) {
        const page = await browser.newPage({ viewport: { width, height: 900 } });
        await page.goto(base + '/login');
        await page.locator('input[name="phone"]').fill('09123456789');
        await page.locator('input[name="password"]').fill('password');
        await page.locator('form.auth-form button[type="submit"]').click();
        await page.waitForURL(url => /^\/admin\/?$/.test(url.pathname));
        await page.goto(base + '/admin/products');
        const filter = page.locator('#admin-product-filters');
        await filter.locator('[name="q"]').fill('ui-qa-product');
        await filter.locator('[name="category_id"]').selectOption('1');
        await filter.locator('[name="status"]').selectOption('active');
        await filter.locator('[name="stock"]').selectOption('in_stock');
        await filter.locator('[name="sort"]').selectOption('price_asc');
        await filter.locator('[name="per_page"]').selectOption('30');
        await Promise.all([
            page.waitForURL(url => url.searchParams.get('q') === 'ui-qa-product'),
            filter.locator('button[type="submit"]').click(),
        ]);
        await page.locator('[data-admin-product-id]').first().waitFor();
        assert.deepEqual(await page.locator('[data-admin-product-id]').evaluateAll(rows => rows.map(row => row.dataset.adminProductId)), ['1']);
        assert.equal(await filter.locator('[name="sort"]').inputValue(), 'price_asc');
        await filter.locator('[name="q"]').fill('no-matching-product-qa');
        await Promise.all([
            page.waitForURL(url => url.searchParams.get('q') === 'no-matching-product-qa'),
            filter.locator('button[type="submit"]').click(),
        ]);
        assert.equal(await page.locator('[data-admin-product-id]').count(), 0);
        await page.goto(base + '/admin/cheques?tab=requests');
        await page.locator('.cheque-hub-tabs').waitFor();
        assert.equal(await page.locator('.cheque-hub-tabs a[aria-current="page"]').innerText(), 'درخواست‌ها');
        const alertResponse = await page.request.get(base + '/admin/cheques/alerts');
        assert.ok(alertResponse.ok());
        assert.deepEqual(await alertResponse.json(), { requests: 0, payments: 0 });
        await page.locator('.cheque-hub-tabs a').filter({hasText:'مشتریان مجاز'}).click();
        await page.waitForURL(url => url.searchParams.get('tab') === 'customers');
        assert.ok(await page.locator('.cheque-customer-table').isVisible());
        assert.ok(await page.locator('[data-cheque-alerts]').isVisible());
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2));
        await page.close();
    }
} finally { await browser.close(); }
console.log('Admin product filter form and unified cheque navigation verified.');
