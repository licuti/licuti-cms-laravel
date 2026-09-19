const { chromium } = require('playwright');
const BASE = 'http://127.0.0.1:8000';
const results = [];
let browser, page;

function log(mod, step, ok, detail = '') {
  results.push({ mod, step, ok, detail });
  console.log(`[${ok ? 'PASS' : 'FAIL'}] ${mod} :: ${step}${detail ? ' :: ' + detail : ''}`);
}

async function checkPage(mod, step) {
  const url = page.url();
  const title = await page.title();
  const bodyText = await page.evaluate(() => document.body.innerText.slice(0, 5000));
  const onLogin = url.includes('/admin/login') || /Page Expired/i.test(title);
  const err = /Whoops|Exception|Fatal error|SQLSTATE|ParseError|Undefined variable|Undefined array|ErrorException/i.test(bodyText);
  if (onLogin) { log(mod, step, false, `login page: ${url}`); return false; }
  if (err) { log(mod, step, false, 'err: ' + bodyText.slice(0, 250).replace(/\n/g, ' ')); return false; }
  log(mod, step, true);
  return true;
}

async function clickSave() {
  await page.locator('button[type="submit"][name="submit_action"][value="save"]').first().click();
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(300);
}

// find row containing text, click its delete (form-confirm) button, confirm modal
async function deleteRow(mod, needle) {
  const row = page.locator('tr').filter({ hasText: needle }).first();
  if (await row.count() === 0) { log(mod, 'delete', false, 'row not found: ' + needle); return false; }
  const delBtn = row.locator('form.form-confirm button, form.form-confirm input[type=submit]').first();
  if (await delBtn.count() === 0) {
    // maybe plain link delete
    const link = row.locator('a').filter({ hasText: /Xoa|Xóa|Delete/i }).first();
    if (await link.count() === 0) { log(mod, 'delete', false, 'no delete control'); return false; }
    await link.click();
  } else {
    await delBtn.click();
    await page.waitForTimeout(500);
    // confirm modal (SweetAlert2 or bootstrap modal)
    const swal = page.locator('.swal2-confirm, .swal2-actions button').first();
    if (await swal.count() > 0 && await swal.isVisible()) {
      await swal.click();
    } else {
      const mbtn = page.locator('.modal.show button, [role=dialog] button').filter({ hasText: /Xoa|Xóa|Delete|Dong y|Đồng ý|Confirm/i }).first();
      if (await mbtn.count() > 0 && await mbtn.isVisible()) { await mbtn.click(); }
      else { await delBtn.evaluate(f => f.form.requestSubmit()); }
    }
  }
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(300);
  const stillThere = await page.locator('tr').filter({ hasText: needle }).count();
  const ok = stillThere === 0;
  log(mod, 'delete', ok, ok ? '' : 'row still present after delete');
  return ok;
}

async function getEditUrl(mod, needle, editRouteRegex) {
  // find edit link inside row
  const row = page.locator('tr').filter({ hasText: needle }).first();
  const links = row.locator('a');
  const count = await links.count();
  for (let i = 0; i < count; i++) {
    const href = await links.nth(i).getAttribute('href');
    const txt = (await links.nth(i).innerText()) || '';
    if (/Sửa|Sua|Edit/i.test(txt) || (editRouteRegex && editRouteRegex.test(href || ''))) return href;
  }
  return null;
}

async function crud(mod, cfg) {
  await page.goto(`${BASE}${cfg.indexUrl}`, { waitUntil: 'networkidle' });
  if (!(await checkPage(mod, 'index'))) return;
  await page.goto(`${BASE}${cfg.createUrl}`, { waitUntil: 'networkidle' });
  if (!(await checkPage(mod, 'create form'))) return;
  await cfg.fill();
  await clickSave();
  if (!(await checkPage(mod, 'store'))) return;
  // detect silent validation failure (redirect back to create form)
  if (/\/create$/.test(page.url())) {
    const errs = await page.evaluate(() => {
      const out = [];
      document.querySelectorAll('.invalid-feedback, .text-danger').forEach(el => { const t = el.innerText.trim(); if (t && t.length < 200) out.push(t); });
      return out;
    });
    log(mod, 'store', false, 'stayed on create form: ' + JSON.stringify(errs.slice(0, 5)));
    return;
  }
  // success message?
  const successTxt = await page.evaluate(() => /thanh cong|thành công/i.test(document.body.innerText));
  log(mod, 'store success toast', successTxt);
  // index shows record
  await page.goto(`${BASE}${cfg.indexUrl}`, { waitUntil: 'networkidle' });
  if (!(await checkPage(mod, 'index after store'))) return;
  let found = false;
  if (cfg.verifyNeedle) {
    found = await page.evaluate((n) => document.body.innerText.includes(n), cfg.verifyNeedle);
    log(mod, 'record visible in index', found);
    if (!found) return;
  }
  // edit
  if (cfg.editNeedle) {
    await page.goto(`${BASE}${cfg.indexUrl}`, { waitUntil: 'networkidle' });
    const editHref = await getEditUrl(mod, cfg.editNeedle);
    if (!editHref) { log(mod, 'edit form', false, 'no edit link for ' + cfg.editNeedle); return; }
    await page.goto(editHref.startsWith('http') ? editHref : `${BASE}${editHref}`, { waitUntil: 'networkidle' });
    if (!(await checkPage(mod, 'edit form'))) return;
    if (cfg.fillOnEdit) {
      await cfg.fillOnEdit();
      await clickSave();
      if (!(await checkPage(mod, 'update'))) return;
      // validation failure redirects back to form; detect by url still being edit page
      const stillOnForm = /\/edit/.test(page.url());
      if (stillOnForm) {
        const errs = await page.evaluate(() => {
          const out = [];
          document.querySelectorAll('.invalid-feedback, .text-danger').forEach(el => { const t = el.innerText.trim(); if (t && t.length < 200) out.push(t); });
          return out;
        });
        log(mod, 'update', false, 'stayed on edit form: ' + JSON.stringify(errs.slice(0, 4)));
        return;
      }
    }
    await page.goto(`${BASE}${cfg.indexUrl}`, { waitUntil: 'networkidle' });
    if (cfg.updateNeedle) {
      const upd = await page.evaluate((n) => document.body.innerText.includes(n), cfg.updateNeedle);
      log(mod, 'update reflected', upd);
    }
  }
  // delete
  await page.goto(`${BASE}${cfg.indexUrl}`, { waitUntil: 'networkidle' });
  await deleteRow(mod, cfg.deleteNeedle || cfg.updateNeedle || cfg.verifyNeedle);
}

(async () => {
  browser = await chromium.launch({ headless: true });
  page = await browser.newPage();
  page.setDefaultTimeout(20000);
  page.on('pageerror', err => console.log('[pageerror]', err.message.slice(0, 300)));

  // login
  await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', 'admin@licuti.com');
  await page.fill('input[name="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(400);
  log('AUTH', 'login', page.url().includes('/admin/dashboard'), page.url());

  // ===== TAG =====
  await crud('TAG', {
    indexUrl: '/admin/tags', createUrl: '/admin/tags/create',
    fill: async () => { await page.fill('input[name="name"]', 'E2E Tag Smoke'); },
    verifyNeedle: 'E2E Tag Smoke', editNeedle: 'E2E Tag Smoke',
    fillOnEdit: async () => { await page.fill('input[name="name"]', 'E2E Tag Smoke UPD'); },
    updateNeedle: 'E2E Tag Smoke UPD',
  });

  // ===== BANNER =====
  await crud('BANNER', {
    indexUrl: '/admin/banners', createUrl: '/admin/banners/create',
    fill: async () => {
      await page.fill('input[name="translations[vi][title]"]', 'E2E Banner Smoke');
      await page.selectOption('select[name="position"]', { index: 1 });
      await page.fill('input[name="link"]', 'https://example.com');
      await page.fill('input[name="display_order"]', '5');
    },
    verifyNeedle: 'E2E Banner Smoke', editNeedle: 'E2E Banner Smoke',
    fillOnEdit: async () => { await page.fill('input[name="translations[vi][title]"]', 'E2E Banner Smoke UPD'); },
    updateNeedle: 'E2E Banner Smoke UPD',
  });

  // ===== MENU =====
  await crud('MENU', {
    indexUrl: '/admin/menus', createUrl: '/admin/menus/create',
    fill: async () => {
      await page.fill('input[name="name"]', 'E2E Menu Smoke');
      await page.fill('input[name="items[0][title]"]', 'Trang chu E2E');
      await page.fill('input[name="items[0][url]"]', '/');
    },
    verifyNeedle: 'E2E Menu Smoke', editNeedle: 'E2E Menu Smoke',
    fillOnEdit: async () => { await page.fill('input[name="name"]', 'E2E Menu Smoke UPD'); },
    updateNeedle: 'E2E Menu Smoke UPD',
  });

  // ===== CATEGORY =====
  await crud('CATEGORY', {
    indexUrl: '/admin/categories', createUrl: '/admin/categories/create',
    fill: async () => {
      await page.fill('input[name="translations[vi][name]"]', 'E2E Category Smoke');
      await page.fill('input[name="display_order"]', '3');
    },
    verifyNeedle: 'E2E Category Smoke', editNeedle: 'E2E Category Smoke',
    fillOnEdit: async () => { await page.fill('input[name="translations[vi][name]"]', 'E2E Category Smoke UPD'); },
    updateNeedle: 'E2E Category Smoke UPD',
  });

  // ===== BRAND =====
  await crud('BRAND', {
    indexUrl: '/admin/brands', createUrl: '/admin/brands/create',
    fill: async () => {
      await page.fill('input[name="translations[vi][name]"]', 'E2E Brand Smoke');
      await page.fill('input[name="display_order"]', '7');
      await page.fill('input[name="website"]', 'https://example.com');
    },
    verifyNeedle: 'E2E Brand Smoke', editNeedle: 'E2E Brand Smoke',
    fillOnEdit: async () => { await page.fill('input[name="translations[vi][name]"]', 'E2E Brand Smoke UPD'); },
    updateNeedle: 'E2E Brand Smoke UPD',
  });

  // ===== PRODUCT ATTRIBUTE =====
  await crud('PRODUCT_ATTRIBUTE', {
    indexUrl: '/admin/product-attributes', createUrl: '/admin/product-attributes/create',
    fill: async () => {
      await page.fill('input[name="translations[vi][name]"]', 'E2E Attr Smoke');
      await page.fill('input[name="code"]', 'e2e_attr');
      await page.selectOption('select[name="type"]', 'select');
      await page.fill('input[name="values[0][value]"]', 'Red');
    },
    verifyNeedle: 'E2E Attr Smoke', editNeedle: 'E2E Attr Smoke',
    fillOnEdit: async () => { await page.fill('input[name="translations[vi][name]"]', 'E2E Attr Smoke UPD'); },
    updateNeedle: 'E2E Attr Smoke UPD',
  });

  // ===== PRODUCT =====
  await crud('PRODUCT', {
    indexUrl: '/admin/products', createUrl: '/admin/products/create',
    fill: async () => {
      await page.fill('input[name="translations[vi][name]"]', 'E2E Product Smoke');
      await page.fill('input[name="price"]', '199000');
      await page.fill('input[name="sku"]', 'E2E-001');
      // publish-box status select
      const statusSel = page.locator('select[name="status"]');
      if (await statusSel.count() > 0) { await statusSel.selectOption('published'); }
      // category + brand selects if present
      const catSel = page.locator('select[name="category_id"]');
      if (await catSel.count() > 0) { const opts = await catSel.locator('option').count(); if (opts > 1) await catSel.selectOption({ index: 1 }); }
      const brandSel = page.locator('select[name="brand_id"]');
      if (await brandSel.count() > 0) { const opts = await brandSel.locator('option').count(); if (opts > 1) await brandSel.selectOption({ index: 1 }); }
    },
    verifyNeedle: 'E2E Product Smoke', editNeedle: 'E2E Product Smoke',
    fillOnEdit: async () => { await page.fill('input[name="translations[vi][name]"]', 'E2E Product Smoke UPD'); },
    updateNeedle: 'E2E Product Smoke UPD',
  });

  await browser.close();
  console.log('\n===== SUMMARY =====');
  const fails = results.filter(r => !r.ok);
  console.log(`Total ${results.length} | PASS ${results.filter(r => r.ok).length} | FAIL ${fails.length}`);
  fails.forEach(f => console.log(`  FAIL ${f.mod} :: ${f.step} :: ${f.detail}`));
  process.exit(fails.length ? 1 : 0);
})().catch(e => { console.error('FATAL', e); process.exit(2); });
