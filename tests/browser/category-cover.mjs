import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';
import assert from 'node:assert/strict';

const base = process.env.UI_BASE_URL || 'http://127.0.0.1:8000';
const browser = await chromium.launch();
await mkdir('artifacts/ui-smoke', { recursive: true });
try {
  for (const width of [390, 543, 1440]) {
    const context = await browser.newContext({ viewport: { width, height: 900 }, reducedMotion: 'reduce' });
    const page = await context.newPage();
    await page.goto(base + '/login');
    await page.locator('input[name="phone"]').fill('09123456789');
    await page.locator('input[name="password"]').fill('password');
    await page.locator('form.auth-form button[type="submit"]').click();
    await page.waitForURL('**/admin/**');
    await page.goto(base + '/admin/categories/1/edit');
    const picker = page.locator('[data-category-cover]');
    await picker.waitFor();
    const data = await page.evaluate(() => {
      const canvas = document.createElement('canvas');
      canvas.width = 1280; canvas.height = 960;
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#f5bbd0'; ctx.fillRect(0,0,1280,960);
      ctx.fillStyle = '#254b63'; ctx.fillRect(300,120,480,420);
      return canvas.toDataURL('image/png').split(',')[1];
    });
    await picker.locator('input[type=file]').setInputFiles({name:'qa.png',mimeType:'image/png',buffer:Buffer.from(data,'base64')});
    const live = picker.locator('[data-media-preview] canvas');
    await live.waitFor();
    await picker.locator('[data-media-zoom]').fill('1.5');
    const editor = picker.locator('[data-media-canvas]');
    const bounds = await editor.boundingBox();
    await page.mouse.move(bounds.x + bounds.width/2, bounds.y + bounds.height/2);
    await page.mouse.down();
    await page.mouse.move(bounds.x + bounds.width/2 + 20, bounds.y + bounds.height/2 + 10);
    await page.mouse.up();
    const geometry = await live.evaluate(el => ({mask:getComputedStyle(el).clipPath,width:el.width,height:el.height}));
    assert.equal(geometry.width,960);
    assert.equal(geometry.height,720);
    assert.notEqual(geometry.mask,'none');
    await picker.scrollIntoViewIfNeeded();
    await page.screenshot({path:'artifacts/ui-smoke/'+width+'-category-crop.png'});
    await picker.locator('[data-media-apply]').click();
    await picker.locator('[data-media-preview] img').waitFor();
    const crop = await picker.locator('input[type=file]').evaluate(async el => {
      const bitmap = await createImageBitmap(el.files[0]);
      const result = {width:bitmap.width,height:bitmap.height};
      bitmap.close();
      return result;
    });
    assert.deepEqual(crop,{width:960,height:720});
    await page.locator('form').filter({has:picker}).locator('button[type=submit]').click();
    await page.waitForURL('**/admin/categories');
    for (const path of ['/','/categories']) {
      await page.goto(base + path);
      const image = page.locator('.janan-category-card[href$="/categories/ui-qa-category"] .janan-category-card__frame img');
      await image.waitFor();
      await image.evaluate(el => el.decode());
      const rendered = await image.evaluate(el => ({mask:getComputedStyle(el).clipPath,width:el.naturalWidth,height:el.naturalHeight,ratio:el.parentElement.getBoundingClientRect().width/el.parentElement.getBoundingClientRect().height}));
      assert.equal(rendered.mask,geometry.mask);
      assert.equal(rendered.width,960);
      assert.equal(rendered.height,720);
      assert.ok(Math.abs(rendered.ratio-4/3)<0.02);
      await image.scrollIntoViewIfNeeded();
      await page.screenshot({path:'artifacts/ui-smoke/'+width+'-category-'+(path==='/'?'home':'directory')+'.png'});
    }
    await context.close();
  }
} finally { await browser.close(); }
console.log('Category upload, crop and public card geometry verified.');
