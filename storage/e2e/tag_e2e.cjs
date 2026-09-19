const { chromium } = require('playwright');

const BASE = 'http://127.0.0.1:8000';
const results = [];
let browser, page;

function log(mod, step, ok, detail = '') {
  const tag = ok ? 'PASS' : 'FAIL';
  results.push({ mod, step, ok, detail });
  console.log(`[${tag}] ${mod} :: ${step}${detail ? ' :: ' + detail : ''}`);
}

async function ensureNoError(mod, step) {
  // check for laravel error page / exception in DOM
  const bodyText = await page.evaluate(() => document.body.innerText.slice(0, 3000));
  const bad = /Whoops|Exception|Fatal error|SQLSTATE|ParseError|Undefined variable|Undefined array/i.test(bodyText);
  if (bad) {
    log(mod, step, false, 'page contains error: ' + bodyText.slice(0, 200).replace(/\n/g, ' '));
    return false;
  }
  // also check title not login page
  const title = await page.title();
  if (/ang nhap|Login/i.test(title)) {
    log(mod, step, false, 'redirected to login page');
    return false;
  }
  log(mod, step, true);
  return true;
}

async function login() {
  await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', 'admin@licuti.com');
  await page.fill('input[name="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
  const title = await page.title();
  const ok = !/ang nhap|Login|Page Expired/i.test(title);
  log('AUTH', 'login', ok, 'title=' + title);
  return ok;
}

async function submitForm(mod, mode) {
  // generic submit: click the primary submit button
  const btn = page.locator('button[type="submit"][name="submit_action"][value="save"]').first();
  await btn.click();
  await page.waitForLoadState('networkidle');
  return ensureNoError(mod, mode + ' submit');
}

async function crudModule(mod, opts) {
  const { indexUrl, createUrl, fillForm, editFinder, editUrl, verifyIndex } = opts;
  // 1. index
  await page.goto(`${BASE}${indexUrl}`, { waitUntil: 'networkidle' });
  if (!(await ensureNoError(mod, 'index'))) return;
  // 2. create form
  await page.goto(`${BASE}${createUrl}`, { waitUntil: 'networkidle' });
  if (!(await ensureNoError(mod, 'create form'))) return;
  // 3. fill + store
  await fillForm();
  await page.locator('button[type="submit"][name="submit_action"][value="save"]').first().click();
  await page.waitForLoadState('networkidle');
  if (!(await ensureNoError(mod, 'store'))) return;
  // success toast?
  const txt = await page.evaluate(() => document.body.innerText);
  if (/thanh cong|thành công|success/i.test(txt)) { /* good */ }
  // 4. verify record in index
  await page.goto(`${BASE}${indexUrl}`, { waitUntil: 'networkidle' });
  if (!(await ensureNoError(mod, 'index after store'))) return;
  if (verifyIndex) {
    const found = await page.evaluate((needle) => document.body.innerText.includes(needle), verifyIndex);
    log(mod, 'store persisted', found, 'looking for "' + verifyIndex + '"');
  }
  // 5. edit form
  const uuid = await editFinder();
  if (!uuid) { log(mod, 'edit form', false, 'could not find created record uuid'); return; }
  await page.goto(`${BASE}${editUrl(uuid)}`, { waitUntil: 'networkidle' });
  if (!(await ensureNoError(mod, 'edit form'))) return;
  // 6. update
  if (opts.updateForm) { await opts.updateForm(); }
  await page.locator('button[type="submit"][name="submit_action"][value="save"]').first().click();
  await page.waitForLoadState('networkidle');
  await ensureNoError(mod, 'update');
  // 7. delete via row action (uses confirm modal)
  await page.goto(`${BASE}${indexUrl}`, { waitUntil: 'networkidle' });
  const deleted = await opts.deleteAction();
  log(mod, 'delete', deleted);
}

(async () => {
  browser = await chromium.launch({ headless: true });
  page = await browser.newPage();
  page.setDefaultTimeout(15000);
  page.on('console', msg => { if (msg.type() === 'error') console.log('[console.error]', msg.text().slice(0, 200)); });
  page.on('pageerror', err => console.log('[pageerror]', err.message.slice(0, 200)));

  if (!(await login())) { await browser.close(); return; }

  // ============ TAG ============
  await crudModule('TAG', {
    indexUrl: '/admin/tags',
    createUrl: '/admin/tags/create',
    fillForm: async () => {
      await page.fill('input[name="name"]', 'E2E Tag Test');
    },
    editFinder: async () => {
      const rows = await page.locator('tr').filter({ hasText: 'E2E Tag Test' }).count();
      return rows > 0 ? 'found' : null;
    },
    editUrl: () => '/admin/tags',
    verifyIndex: 'E2E Tag Test',
    deleteAction: async () => {
      // find delete button for this row
      const row = page.locator('tr').filter({ hasText: 'E2E Tag Test' });
      const delBtn = row.locator('button, a').filter({ hasText: /Xoa|Xóa|Delete/i }).first();
      if (await delBtn.count() === 0) return false;
      await delBtn.click();
      await page.waitForTimeout(400);
      const confirmBtn = page.locator('.modal.show button, [role="dialog"] button').filter({ hasText: /Xoa|Xóa|Delete|Confirm/i }).first();
      if (await confirmBtn.count() > 0) { await confirmBtn.click(); await page.waitForLoadState('networkidle'); }
      const stillThere = await page.locator('tr').filter({ hasText: 'E2E Tag Test' }).count();
      return stillThere === 0;
    },
  });

  await browser.close();

  console.log('\n===== SUMMARY =====');
  const fails = results.filter(r => !r.ok);
  console.log(`Total: ${results.length} | PASS: ${results.filter(r => r.ok).length} | FAIL: ${fails.length}`);
  fails.forEach(f => console.log(`  FAIL: ${f.mod} :: ${f.step} :: ${f.detail}`));
  process.exit(fails.length > 0 ? 1 : 0);
})().catch(e => { console.error('FATAL', e); process.exit(2); });
