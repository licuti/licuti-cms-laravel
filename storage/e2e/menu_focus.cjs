const { chromium } = require('playwright');
const BASE = 'http://127.0.0.1:8000';
let browser, page;

(async () => {
  browser = await chromium.launch({ headless: true });
  page = await browser.newPage();
  page.setDefaultTimeout(15000);
  await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', 'admin@licuti.com');
  await page.fill('input[name="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(300);

  await page.goto(`${BASE}/admin/menus`, { waitUntil: 'networkidle' });
  const row = page.locator('tr').filter({ hasText: 'DBG Menu' }).first();
  const links = row.locator('a');
  const n = await links.count();
  let editHref = null;
  for (let i = 0; i < n; i++) {
    const t = (await links.nth(i).innerText()) || '';
    if (/Sửa|Sua|Edit/i.test(t)) { editHref = await links.nth(i).getAttribute('href'); break; }
  }
  console.log('menu edit href:', editHref);
  if (!editHref) { await browser.close(); return; }
  await page.goto(editHref, { waitUntil: 'networkidle' });
  await page.fill('input[name="name"]', 'DBG Menu UPD');
  const [resp] = await Promise.all([
    page.waitForResponse(r => r.request().method() === 'POST'),
    page.locator('button[type="submit"][name="submit_action"][value="save"]').first().click(),
  ]);
  console.log('menu update status:', resp.status(), '->', resp.url());
  await page.waitForTimeout(400);
  await page.goto(`${BASE}/admin/menus`, { waitUntil: 'networkidle' });
  const body = await page.evaluate(() => document.body.innerText);
  console.log('index has UPD:', body.includes('DBG Menu UPD'), '| old still:', body.includes('DBG Menu'));
  await browser.close();
})().catch(e => console.error('FATAL', e.message));
