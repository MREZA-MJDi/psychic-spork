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
        await page.goto(base + '/admin/cheques?tab=payments');
        const workspace = page.locator('.cheque-workspace');
        await workspace.waitFor();
        const row = workspace.locator('[data-cheque-row]').filter({ hasText: 'QA-WORKSPACE-001' });
        await row.waitFor();
        assert.equal(await workspace.locator('#cheque-detail').count(), 0);
        assert.equal(await row.locator('form').count(), 0);
        const overflow = () => page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2);
        assert.ok(await overflow());
        await row.getByRole('link', {name:'جزئیات و بررسی'}).click();
        await page.waitForURL(url => url.searchParams.has('cheque_id'));
        const detail = workspace.locator('#cheque-detail');
        await detail.waitFor();
        assert.equal(await detail.getByRole('button', {name:'تأیید و آزادسازی سفارش'}).count(), 1);
        const date = detail.locator('[data-admin-date-format="day"]');
        await date.waitFor();
        assert.equal(await date.innerText(), new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
            year:'numeric', month:'2-digit', day:'2-digit', timeZone:'UTC'
        }).format(new Date('2026-12-22')));
        const option = detail.locator('details').filter({hasText:'درخواست ارسال اصل چک'});
        assert.equal(await option.locator('textarea').isVisible(), false);
        await option.locator('summary').click();
        await option.locator('textarea').fill('QA instruction only');
        assert.ok(await option.locator('textarea').isVisible());
        assert.ok(await overflow());
        await detail.getByRole('link', {name:'بستن',exact:true}).click();
        await page.waitForURL(url => !url.searchParams.has('cheque_id'));
        assert.equal(await workspace.locator('#cheque-detail').count(), 0);
        await workspace.getByRole('link',{name:'مشتریان مجاز',exact:true}).click();
        await page.waitForURL(url=>url.searchParams.get('tab')==='customers');
        const customer = workspace.locator('tbody tr').filter({hasText:'Cheque Workspace QA'});
        await customer.getByRole('link',{name:'مدیریت مجوز'}).click();
        await page.waitForURL(url=>url.searchParams.has('edit_customer'));
        assert.equal(await workspace.locator('#cheque-customer-detail input[name="max_order_amount"]').count(),1);
        assert.ok(await overflow());
        await page.close();
    }
} finally { await browser.close(); }
console.log('Cheque workspace detail, current actions, Jalali date and responsive layout verified.');
