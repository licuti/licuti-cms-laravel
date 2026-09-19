const { chromium } = require('playwright');
const BASE = 'http://127.0.0.1:8000';
let browser, page;

(async () => {
  browser = await chromium.launch({ headless: true });
  page = await browser.newPage();
  page.setDefaultTimeout(15000);
  page.on('dialog', d => d.dismiss());
  await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', 'admin@licuti.com');
  await page.fill('input[name="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(300);

  // find first tag's edit link
  await page.goto(`${BASE}/admin/tags`, { waitUntil: 'networkidle' });
  const row = page.locator('tr').filter({ hasText: 'E2E Tag Smoke' }).first();
  const links = row.locator('a');
  const n = await links.count();
  let editHref = null;
  for (let i = 0; i < n; i++) {
    const t = (await links.nth(i).innerText()) || '';
    if (/Sửa|Sua|Edit/i.test(t)) { editHref = await links.nth(i).getAttribute('href'); break; }
  }
  console.log('edit href:', editHref);
  if (!editHref) { console.log('no edit link found'); await browser.close(); return; }

  // capture PUT response
  await page.goto(editHref, { waitUntil: 'networkidle' });
  await page.fill('input[name="name"]', 'E2E Tag Smoke UPD');
  const [resp] = await Promise.all([
    page.waitForResponse(r => r.request().method() === 'POST' || r.request().method() === 'PUT'),
    page.locator('button[type="submit"][name="submit_action"][value="save"]').first().click(),
  ]);
  console.log('update response status:', resp.status(), '->', resp.url());

  // check DB via a small API? use mysql through node child_process is heavy; instead reload index
  await page.waitForTimeout(500);
  await page.goto(`${BASE}/admin/tags`, { waitUntil: 'networkidle' });
  const body = await page.evaluate(() => document.body.innerText);
  console.log('index has UPD name:', body.includes('E2E Tag Smoke UPD'));
  console.log('index has old name count:', (body.match(/E2E Tag Smoke(?! UPD)/g) || []).length);

  // now delete the UPD row via form-confirm + sweetalert
  const row2 = page.locator('tr').filter({ hasText: 'E2E Tag Smoke UPD' }).first();
  const delForm = row2.locator('form.form-confirm');
  console.log('delete form count:', await delForm.count());
  await delForm.locator('button').first().click();
  await page.waitForTimeout(600);
  const swalBtn = page.locator('.swal2-confirm');
  console.log('swal visible:', await swalBtn.count(), await swalBtn.isVisible().catch(() => false));
  if (await swalBtn.count() > 0) {
    const [dresp] = await Promise.all([
      page.waitForResponse(r => r.request().method() === 'DELETE').catch(() => null),
      swalBtn.click(),
    ]);
    console.log('delete response:', dresp ? dresp.status() : 'no-response');
  }
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(400);
  const body2 = await page.evaluate(() => document.body.innerText);
  console.log('after delete, UPD still present:', body2.includes('E2E Tag Smoke UPD'));

  await browser.close();
})().catch(e => console.error('FATAL', e.message, e.stack));
