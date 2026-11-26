import { chromium } from 'playwright';
import assert from 'node:assert/strict';
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
    assert.ok(added.ok(), 'Fixture product should be added to cart');
    let offline = false;
    await page.route('**/locations/provinces', route => route.fulfill({
      status: offline ? 503 : 200, json: offline ? {} : [{id:7,name:'تهران'},{id:8,name:'فارس'}],
    }));
    await page.route('**/locations/cities?*', route => route.fulfill({
      json: new URL(route.request().url()).searchParams.get('province') === '7'
        ? [{name:'تهران'},{name:'ورامین'}] : [{name:'شیراز'}],
    }));
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
    await city.selectOption('شیراز');
    const values = await picker.evaluate(el => {
      const form = new FormData(el.closest('form'));
      return [form.getAll('shipping_province'), form.getAll('shipping_city')];
    });
    assert.deepEqual(values, [['فارس'], ['شیراز']]);
    offline = true;
    await page.reload();
    const manualProvince = picker.locator('[data-location-province-manual]');
    const manualCity = picker.locator('[data-location-city-manual]');
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
