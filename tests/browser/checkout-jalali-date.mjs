import { chromium } from 'playwright';
import assert from 'node:assert/strict';
import { parsePersianDate } from '../../resources/js/jalali-date.js';

for (const [input, expected] of [
    ['۱۴۰۵/۰۹/۰۶', '2026-11-27'],
    ['1403/12/30', '2025-03-20'],
    ['1404/12/30', null],
    ['1404/07/31', null],
    ['١٤٠٤/٠١/٠١', '2025-03-21'],
]) assert.equal(parsePersianDate(input), expected);

const base = process.env.UI_BASE_URL || 'http://127.0.0.1:8000';
const browser = await chromium.launch();
try {
    for (const width of [390, 1440]) {
        const page = await browser.newPage({ viewport: { width, height: 900 } });
        await page.goto(base + '/cart');
        const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');
        const added = await page.request.post(base + '/cart/1', {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
            data: { quantity: 1 },
        });
        assert.ok(added.ok());
        // Exercise the production JS bundle and actual checkout form without granting
        // a fixture account permission to submit a real cheque payment.
        await page.route(base + '/checkout', async route => {
            const response = await route.fetch();
            const html = await response.text();
            const body = html.replace(/(<form\b[^>]*class="checkout-form"[^>]*>)/, '$1<fieldset id="qa-cheque"><label>تاریخ سررسید<input type="date" id="qa-date" name="due_date" min="2026-11-27" value="2026-11-28" data-jalali-date required></label></fieldset>');
            assert.notEqual(body, html);
            await route.fulfill({ response, body });
        });
        await page.goto(base + '/checkout');
        const display = page.locator('#qa-date-display');
        const native = page.locator('#qa-date');
        const picker = page.locator('#qa-cheque .jalali-date');
        await display.waitFor();
        assert.equal(await display.inputValue(), '۱۴۰۵/۰۹/۰۷');
        assert.equal(await native.inputValue(), '2026-11-28');
        await picker.locator('.jalali-date__toggle').click();
        const calendar = picker.locator('.jalali-date__panel');
        await calendar.waitFor();
        const bounds = await calendar.boundingBox();
        assert.ok(bounds.x >= -1 && bounds.x + bounds.width <= width + 1);
        assert.equal(await calendar.getByRole('button', { name: '۱۴۰۵/۰۹/۰۵', exact: true }).isDisabled(), true);
        await calendar.getByRole('button', { name: 'ماه بعد' }).click();
        assert.match(await calendar.innerText(), /دی/);
        await calendar.getByRole('button', { name: 'ماه قبل' }).click();
        await calendar.getByRole('button', { name: '۱۴۰۵/۰۹/۰۶', exact: true }).click();
        assert.equal(await native.inputValue(), '2026-11-27');
        const payload = () => native.evaluate(el => new FormData(el.form).getAll('due_date'));
        assert.deepEqual(await payload(), ['2026-11-27']);
        await display.fill('۱۴۰۵/۰۹/۰۵');
        assert.equal(await display.evaluate(el => el.checkValidity()), false);
        assert.equal(await native.inputValue(), '');
        await display.fill('1405/10/01');
        assert.equal(await display.evaluate(el => el.checkValidity()), true);
        assert.deepEqual(await payload(), ['2026-12-22']);
        await picker.evaluate(el => { el.closest('fieldset').disabled = true; });
        assert.deepEqual(await payload(), []);
        await page.close();
    }
} finally {
    await browser.close();
}
console.log('Persian checkout date conversion, calendar navigation, validation and ISO payload verified.');
