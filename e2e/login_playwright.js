const { chromium } = require('playwright');
(async () => {
  const APP_URL = process.env.APP_URL || 'https://projectakhir1.serverdata.asia';
  const USERNAME = process.env.E2E_USER || 'superadmin';
  const PASSWORD = process.env.E2E_PASS || 'ChangeMe123!';
  console.log('APP_URL=', APP_URL);
  const execPath = process.env.CHROME_PATH || null;
  const launchOpts = { headless: true, args: ['--no-sandbox'], ignoreHTTPSErrors: true };
  if (execPath) launchOpts.executablePath = execPath;
  const browser = await chromium.launch(launchOpts);
  const context = await browser.newContext();
  const page = await context.newPage();
  try {
    console.log('Navigating to login page...');
    await page.goto(`${APP_URL}/login`, { waitUntil: 'networkidle' });
    const csrf = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    console.log('Found csrf meta:', !!csrf);
    // fill form (common selectors)
    await page.fill('input[name="username"]', USERNAME).catch(()=>{});
    await page.fill('input[name="email"]', USERNAME).catch(()=>{});
    await page.fill('input[name="password"]', PASSWORD);
    // submit: try button[type=submit]
    const [response] = await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }).catch(e=>null),
      page.click('button[type="submit"]').catch(async ()=>{
        // fallback: submit form
        await page.evaluate(()=>{ const f = document.querySelector('form'); if(f) f.submit(); });
      })
    ]);
    console.log('Navigation response URL:', page.url());
    const cookies = await context.cookies();
    const laravel = cookies.find(c=>c.name.includes('laravel'));
    console.log('Cookies count:', cookies.length);
    if (laravel) {
      console.log('laravel cookie:', { name: laravel.name, domain: laravel.domain, secure: laravel.secure, sameSite: laravel.sameSite });
    } else {
      console.log('laravel cookie not found');
    }
    // dump final page title and short content
    console.log('Final title:', await page.title());
    const text = await page.locator('body').innerText().catch(()=>'');
    console.log('Body snippet:', text.slice(0,300).replace(/\n+/g,' '));
  } catch (err) {
    console.error('E2E error:', err && err.message ? err.message : err);
  } finally {
    await browser.close();
  }
})();