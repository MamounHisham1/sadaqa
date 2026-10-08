/* Part 2: submit the real form, capture success + toast + stream page states. */
const path = require('path');
const puppeteer = require('/home/mamoun/.npm/_npx/110f701c48e68d66/node_modules/puppeteer-core');

const OUT = '/home/mamoun/ai/sadaqa/brag-output/composition/assets/captures';
const BASE = 'http://127.0.0.1:8099';
const CHROME = '/home/mamoun/.cache/puppeteer/chrome/linux-152.0.7977.54/chrome-linux64/chrome';

(async () => {
  const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: 'new',
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--force-color-profile=srgb', '--hide-scrollbars', '--lang=ar', '--autoplay-policy=no-user-gesture-required'],
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1280, height: 800, deviceScaleFactor: 2 });
  await page.evaluateOnNewDocument(() => { localStorage.setItem('theme', 'light'); });

  const shot = (n) => page.screenshot({ path: path.join(OUT, n) });
  const maskDomain = () => page.evaluate(() => {
    document.body.innerHTML = document.body.innerHTML.split('127.0.0.1:8099').join('sadaqa.app');
  });

  // fill + submit the real form
  await page.goto(BASE + '/#create', { waitUntil: 'networkidle0' });
  await page.click('#recipient_name');
  await page.type('#recipient_name', 'أمي', { delay: 20 });
  await page.click('#sender_name');
  await page.type('#sender_name', 'أحمد', { delay: 20 });
  await page.click('#message');
  await page.type('#message', 'رحمة ونور', { delay: 20 });

  await page.click('.form-card button[type="submit"]');
  await page.waitForFunction(() => location.pathname.includes('/streaming/'), { timeout: 60000 });
  await page.waitForNetworkIdle({ idleTime: 800, timeout: 30000 });
  const token = new URL(page.url()).pathname.split('/').pop();
  console.log('real token =', token);
  await new Promise(r => setTimeout(r, 1600));
  await maskDomain();
  await shot('success-banner.png');
  console.log('success-banner ok');

  // click نسخ → toast (the page's own copy handler)
  await page.evaluate(() => { document.getElementById('shareCopy')?.click(); });
  await new Promise(r => setTimeout(r, 600));
  await maskDomain();
  await shot('success-copied.png');
  console.log('success-copied ok');

  // stream page without ?new — before play
  await page.goto(`${BASE}/streaming/${token}`, { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 2500));
  await maskDomain();
  await shot('stream-before.png');
  console.log('stream-before ok');

  // press play for real → pause icon + progress
  await page.click('#playBtn');
  await new Promise(r => setTimeout(r, 2500));
  await maskDomain();
  await shot('stream-playing.png');
  console.log('stream-playing ok');

  // scrolled variant showing the form bottom (reciter pills + submit)
  await page.setViewport({ width: 1280, height: 800, deviceScaleFactor: 2 });
  await page.goto(BASE + '/#create', { waitUntil: 'networkidle0' });
  await page.evaluate(() => { const c = document.querySelector('.form-card'); c.scrollIntoView({ behavior: 'instant', block: 'start' }); window.scrollBy(0, 430); });
  await new Promise(r => setTimeout(r, 500));
  await shot('form-submit-area.png');
  console.log('form-submit-area ok');

  // phone viewport — same live page playing
  await page.setViewport({ width: 390, height: 780, deviceScaleFactor: 3 });
  await page.goto(`${BASE}/streaming/${token}`, { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 2000));
  await page.click('#playBtn');
  await new Promise(r => setTimeout(r, 2000));
  await maskDomain();
  await shot('stream-phone.png');
  console.log('stream-phone ok');

  await browser.close();
  console.log('ALL DONE token=' + token);
})().catch(e => { console.error('CAPTURE FAILED:', e.message); process.exit(1); });
