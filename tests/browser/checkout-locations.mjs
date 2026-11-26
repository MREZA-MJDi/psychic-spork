import { chromium } from 'playwright';
import assert from 'node:assert/strict';
const base = process.env.UI_BASE_URL || 'http://127.0.0.1:8000';
const browser = await chromium.launch();
try {
  for (const width of [390, 1440]) {
    const page = await browser.newPage({ viewport: { width, height: 900 } });
    await page.goto(base + '/');
    const fixtureCard = page.locator('.home-product-card').filter({ hasText: 'محصول آزمایشی جانان' });
    const added = page.waitForResponse(response =>
      new URL(response.url()).pathname === '/cart/1' && response.request().method() === 'POST'
    );
    await fixtureCard.getByRole('button', { name: 'افزودن به سبد' }).click();
    assert.ok((await added).ok(), 'Fixture product should be added from the home card');
    let offline = false;
    await page.route('**/locations/provinces', route => offline
      ? route.fulfill({status: 503, json: {}})
      : route.continue()
    );
    await page.goto(base + '/checkout');
    const picker = page.locator('[data-location-fields]');
    const province = picker.locator('[data-location-province]');
    const city = picker.locator('[data-location-city]');
    await province.waitFor();
    await province.selectOption('تهران');
    await city.locator('option[value="ورامین"]').waitFor({state:'attached'});
    await city.selectOption('ورامین');
    await province.selectOption('فارس');
    await city.locator('option[value="شیراز"]').waitFor({state:'attached'});
    assert.equal(await city.inputValue(),'');
    await city.selectOption('__manual__');
    const manualCity = picker.locator('[data-location-city-manual]');
    await manualCity.fill('شیراز');
    const values = await picker.evaluate(el => {
      const form = new FormData(el.closest('form'));
      return [form.getAll('shipping_province'), form.getAll('shipping_city')];
    });
    assert.deepEqual(values, [['فارس'], ['شیراز']]);
    offline = true;
    await page.reload();
    const manualProvince = picker.locator('[data-location-province-manual]');
    await picker.locator('[data-location-retry]').waitFor();
    await manualProvince.fill('تهران');
    await manualCity.fill('ورامین');
    assert.equal(await manualCity.inputValue(), 'ورامین');
    offline = false;
    await picker.locator('[data-location-retry]').click();
    await city.locator('option[value="ورامین"]').waitFor({state:'attached'});
    assert.equal(await province.inputValue(),'تهران');
    assert.equal(await city.inputValue(),'ورامین');
    await page.close();
  }
} finally { await browser.close(); }
console.log('Checkout location cascade, fallback and form values verified.');
