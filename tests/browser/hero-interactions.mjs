import { chromium } from 'playwright';
import assert from 'node:assert/strict';
import { mkdir } from 'node:fs/promises';
const base=process.env.UI_BASE_URL||'http://127.0.0.1:8000';
const browser=await chromium.launch();
await mkdir('artifacts/ui-smoke',{recursive:true});
try {
  // Use the real server's slide, with distinct fixture titles and prices,
  // to exercise multiple items without changing shared database fixtures.
  const page=await browser.newPage({viewport:{width:1440,height:900},reducedMotion:'reduce'});
  await page.route(base+'/',async route=>{
    const response=await route.fetch();
    const html=await response.text();
    const body=html.replace(/(<script[^>]+id="immersiveHeroData"[^>]*>)([\s\S]*?)(<\/script>)/,(_,start,json,end)=>{
      const first=JSON.parse(json)[0];
      const slides=Array.from({length:8},(_,i)=>({...first,id:i+1,title:'محصول آزمایشی '+(i+1),price:(i+1)+'۰۰٬۰۰۰ تومان'}));
      return start+JSON.stringify(slides)+end;
    });
    await route.fulfill({response,body});
  });
  await page.goto(base+'/');
  const hero=page.locator('[data-immersive-gallery]');
  await page.waitForFunction(()=>document.querySelector('[data-immersive-gallery]')?.dataset.galleryReady==='true');
  const cards=hero.locator('.immersive-grid-item');
  assert.equal(await cards.count(),8);
  const fit=hero.locator('[data-fit]');
  await fit.click();
  await page.waitForFunction(()=>document.querySelector('[data-fit]')?.getAttribute('aria-pressed')==='true');
  await page.waitForTimeout(100);
  const allInView=async()=>await cards.evaluateAll(nodes=>nodes.every(el=>{const r=el.getBoundingClientRect();return r.left>=-1&&r.right<=innerWidth+1&&r.top>=-1&&r.bottom<=innerHeight+1;}));
  assert.ok(await allInView(),'fit should contain all cards');
  await hero.locator('[data-zoom="1"]').click();
  await hero.locator('[data-zoom="0.3"]').click();
  await fit.click();
  await page.waitForTimeout(100);
  await cards.nth(2).click();
  await page.waitForFunction(()=>document.querySelector('[data-immersive-gallery]')?.dataset.productReady==='true');
  assert.equal(await hero.locator('#immersiveProductPrice').textContent(),'3۰۰٬۰۰۰ تومان');
  assert.equal(await hero.getAttribute('aria-modal'),'true');
  assert.equal(await page.evaluate(()=>document.activeElement.id),'immersiveCloseButton');
  await page.keyboard.press('Tab');
  assert.equal(await page.evaluate(()=>document.activeElement.id),'immersiveSlideLink');
  await page.setViewportSize({width:390,height:844});
  await page.waitForTimeout(200);
  const panel=await hero.locator('#immersiveTitleOverlay').boundingBox();
  assert.ok(panel.x>=0&&panel.x+panel.width<=391&&panel.y>=0&&panel.y+panel.height<=845);
  await page.screenshot({path:'artifacts/ui-smoke/hero-resize-open.png'});
  await page.keyboard.press('Escape');
  await page.waitForFunction(()=>!document.body.classList.contains('immersive-zoom-lock'));
  assert.equal(await cards.nth(2).evaluate(el=>document.activeElement===el),true);
  await fit.click();
  await page.waitForTimeout(100);
  assert.ok(await allInView(),'fit should remain correct after resize');
  await cards.nth(0).click();
  await page.waitForFunction(()=>document.querySelector('[data-immersive-gallery]')?.dataset.productReady==='true');
  await hero.locator('#immersiveCloseButton').click();
  await page.waitForFunction(()=>!document.body.classList.contains('immersive-zoom-lock'));
  assert.equal(await hero.locator('.immersive-scaling-overlay').count(),0);
  await page.close();

  const mobile=await browser.newPage({viewport:{width:390,height:844},hasTouch:true,isMobile:true,reducedMotion:'reduce'});
  await mobile.goto(base+'/');
  await mobile.waitForFunction(()=>document.querySelector('[data-immersive-gallery]')?.dataset.galleryReady==='true');
  await mobile.locator('.immersive-grid-item').first().tap();
  await mobile.waitForFunction(()=>document.querySelector('[data-immersive-gallery]')?.dataset.productReady==='true');
  await mobile.locator('#immersiveCloseButton').tap();
  await mobile.waitForFunction(()=>!document.body.classList.contains('immersive-zoom-lock'));
  await mobile.close();
} finally {await browser.close();}
console.log('Hero fit, rapid zoom, product data, keyboard focus, resize and touch verified.');
