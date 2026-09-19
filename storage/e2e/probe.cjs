const { chromium } = require('playwright');

const BASE = 'http://127.0.0.1:8000';
const results = [];
let browser, page;

function log(mod, step, ok, detail = '') {
  const tag = ok ? 'PASS' : 'FAIL';
  results.push({ mod, step, ok, detail });
  console.log(`[${tag}] ${mod} :: ${step}${detail ? ' :: ' + detail : ''}`);
}

async function checkPage(mod, step) {
  const url = page.url();
  const title = await page.title();
  const bodyText = await page.evaluate(() => document.body.innerText.slice(0, 4000));
  const onLogin = url.includes('/admin/login') || /Page Expired/i.test(title);
  const err = /Whoops|Exception|Fatal error|SQLSTATE|ParseError|Undefined variable|Undefined array|ErrorException/i.test(bodyText);
  if (onLogin) { log(mod, step, false, `on login page: ${url} title=${title}`); return false; }
  if (err) { log(mod, step, false, 'error text: ' + bodyText.slice(0, 200).replace(/\n/g, ' ')); return false; }
  log(mod, step, true, url.replace(BASE, ''));
  return true;
}

async function login() {
  await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', 'admin@licuti.com');
  await page.fill('input[name="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(500);
  const ok = page.url().includes('/admin/dashboard') || page.url().includes('/admin');
  log('AUTH', 'login', ok, 'url=' + page.url());
  return ok;
}

async function saveForm(mod, mode) {
  const btn = page.locator('button[type="submit"][name="submit_action"][value="save"]').first();
  await btn.click();
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(300);
  return checkPage(mod, mode);
}

(async () => {
  browser = await chromium.launch({ headless: true });
  page = await browser.newPage();
  page.setDefaultTimeout(15000);
  page.on('pageerror', err => console.log('[pageerror]', err.message.slice(0, 300)));

  if (!(await login())) {
    // dump login page content for debugging
    const html = await page.content();
    require('fs').writeFileSync('storage/e2e/login_dump.html', html);
    await browser.close();
    summary();
    return;
  }

  // ===== TAG =====
  const M = 'TAG';
  await page.goto(`${BASE}/admin/tags`, { waitUntil: 'networkidle' });
  await checkPage(M, 'index');
  await page.goto(`${BASE}/admin/tags/create`, { waitUntil: 'networkidle' });
  await checkPage(M, 'create form');
  // dump form html to inspect field names
  const tagFormHtml = await page.content();
  require('fs').writeFileSync('storage/e2e/tag_form_dump.html', tagFormHtml);
  await browser.close();
  summary();
})();

function summary() {
  console.log('\n===== SUMMARY =====');
  const fails = results.filter(r => !r.ok);
  console.log(`Total: ${results.length} | PASS: ${results.filter(r => r.ok).length} | FAIL: ${fails.length}`);
  fails.forEach(f => console.log(`  FAIL: ${f.mod} :: ${f.step} :: ${f.detail}`));
}
