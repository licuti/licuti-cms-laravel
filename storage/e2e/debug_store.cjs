const { chromium } = require('playwright');
const BASE = 'http://127.0.0.1:8000';
const fs = require('fs');
let browser, page;

(async () => {
  browser = await chromium.launch({ headless: true });
  page = await browser.newPage();
  page.setDefaultTimeout(20000);
  await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', 'admin@licuti.com');
  await page.fill('input[name="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(400);
  console.log('logged in:', page.url());

  const cases = [
    { name: 'banner', url: '/admin/banners/create', fill: async () => {
        await page.fill('input[name="translations[vi][title]"]', 'DBG Banner');
        await page.selectOption('select[name="position"]', { index: 1 });
        await page.fill('input[name="link"]', 'https://example.com');
      }},
    { name: 'menu', url: '/admin/menus/create', fill: async () => {
        await page.fill('input[name="name"]', 'DBG Menu');
        await page.fill('input[name="items[0][title]"]', 'DBG Trang chu');
        await page.fill('input[name="items[0][url]"]', '/');
      }},
    { name: 'category', url: '/admin/categories/create', fill: async () => {
        await page.fill('input[name="translations[vi][name]"]', 'DBG Category');
      }},
    { name: 'pattr', url: '/admin/product-attributes/create', fill: async () => {
        await page.fill('input[name="translations[vi][name]"]', 'DBG Attr');
        await page.fill('input[name="code"]', 'dbg_attr');
        await page.selectOption('select[name="type"]', 'select');
        await page.fill('input[name="values[0][value]"]', 'Red');
      }},
  ];

  for (const c of cases) {
    await page.goto(`${BASE}${c.url}`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(300);
    await c.fill();
    await page.locator('button[type="submit"][name="submit_action"][value="save"]').first().click();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(400);
    const html = await page.content();
    fs.writeFileSync(`storage/e2e/dbg_${c.name}.html`, html);
    // extract validation error text
    const errs = await page.evaluate(() => {
      const out = [];
      document.querySelectorAll('.invalid-feedback, .text-danger, .alert-danger, [class*="error"]').forEach(el => {
        const t = el.innerText.trim();
        if (t && t.length < 300) out.push(t);
      });
      return out;
    });
    console.log(`\n=== ${c.name} === url=${page.url()}`);
    console.log('errors:', JSON.stringify(errs.slice(0, 8), null, 1));
  }

  await browser.close();
})().catch(e => console.error('FATAL', e.message));
