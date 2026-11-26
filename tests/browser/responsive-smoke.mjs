import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';

const base = process.env.UI_BASE_URL || 'http://127.0.0.1:8000';
const widths = [320, 360, 390, 543, 768, 1024, 1440];
const paths = ['/', '/products', '/categories', '/categories/ui-qa-category',
  '/products/ui-qa-product', '/brands', '/brands/ui-qa-brand', '/cart',
  '/about', '/faq', '/shipping', '/returns', '/login'];
const failures = [];
await mkdir('artifacts/ui-smoke', { recursive: true });
const browser = await chromium.launch({ headless: true });

try {
  for (const width of widths) {
    const context = await browser.newContext({
      viewport: { width, height: width === 543 ? 560 : 900 },
      reducedMotion: 'reduce', deviceScaleFactor: 1,
    });
    const page = await context.newPage();
    await page.route('https://maps.google.com/**', route => route.fulfill({contentType:'text/html',body:'<html><body>Map preview (test)</body></html>'}));
    for (const path of paths) {
      const name = path === '/' ? 'home' : path.slice(1).replaceAll('/', '-');
      try {
        const response = await page.goto(new URL(path, base).href, { waitUntil: 'domcontentloaded' });
        if (!response?.ok()) throw new Error('HTTP ' + response?.status());
        await page.evaluate(() => document.fonts.ready);
        const layout = await page.evaluate(() => ({
          viewport: document.documentElement.clientWidth,
          scroll: document.documentElement.scrollWidth,
          font: getComputedStyle(document.body).fontFamily,
          fontLoaded: document.fonts.check('400 14px IRANYekanX'),
        }));
        if (layout.scroll > layout.viewport + 2) throw new Error('Horizontal document overflow: ' + JSON.stringify(layout));
        if (!layout.font.includes('IRANYekanX') || !layout.fontLoaded) throw new Error('Unexpected or unloaded body font: ' + layout.font);
        if (path === '/products') {
          const pager = page.locator('.janan-pagination').first();
          await pager.waitFor();
          const next = pager.locator('a[rel="next"]');
          if (!await next.isVisible()) throw new Error('Missing next-page control');
          const bounds = await pager.boundingBox();
          if (!bounds || bounds.x < -2 || bounds.x + bounds.width > width + 2) throw new Error('Pagination outside viewport');
          if ((await pager.innerText()).includes('Showing')) throw new Error('Untranslated pagination');
        }
        if (path === '/about') {
          await page.locator('.contact-map-card iframe').waitFor();
          const map = page.locator('.contact-map-card iframe');
          if (!(await map.getAttribute('src')).includes('maps.google.com')) throw new Error('Contact map missing');
          const bounds = await map.boundingBox();
          if (!bounds || bounds.x < -2 || bounds.x + bounds.width > width + 2) throw new Error('Contact map outside viewport');
        }
        await page.screenshot({ path: 'artifacts/ui-smoke/' + width + '-' + name + '.png', fullPage: true, animations: 'disabled' });
        if (path === '/') {
          const hero = page.locator('[data-immersive-gallery]');
          const item = hero.locator('.immersive-grid-item').first();
          await item.waitFor({ state: 'visible', timeout: 10000 });
          await item.click({ timeout: 10000 });
          await hero.locator('.immersive-title-overlay.is-active').waitFor({ state: 'visible' });
          const panel = hero.locator('#immersiveTitleOverlay');
          const bounds = await panel.boundingBox();
          if (!bounds || bounds.x < -2 || bounds.x + bounds.width > width + 2) throw new Error('Hero inspector outside viewport');
          const expectedPrice = await hero.locator('#immersiveHeroData').evaluate(el => JSON.parse(el.textContent)[0].price);
          if (!expectedPrice || !expectedPrice.includes('تومان')) throw new Error('Fixture hero price missing');
          await page.waitForFunction(expected => {
            const price = document.querySelector('#immersiveProductPrice');
            return price?.textContent.trim() === expected && price.getBoundingClientRect().height > 0 && getComputedStyle(price).visibility === 'visible';
          }, expectedPrice, {timeout: 5000});
          await page.screenshot({ path: 'artifacts/ui-smoke/' + width + '-hero-open.png', animations: 'disabled' });
          await hero.locator('#immersiveCloseButton').click();
          await hero.locator('.immersive-title-overlay.is-active').waitFor({ state: 'hidden' });
        }
      } catch (error) {
        failures.push(width + 'px ' + path + ': ' + error.message);
        await page.screenshot({ path: 'artifacts/ui-smoke/' + width + '-' + name + '-failed.png' }).catch(() => {});
      }
    }
    await context.close();
  }
} finally {
  await browser.close();
}
if (failures.length) {
  console.error(failures.join('\n'));
  process.exitCode = 1;
} else {
  console.log('Responsive smoke checks passed across all configured routes and widths.');
}
