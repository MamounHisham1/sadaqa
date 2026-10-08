/* Capture real sadaqa page states for the brag video — authentic browser pixels. */
const path = require('path');
const puppeteer = require('/home/mamoun/.npm/_npx/110f701c48e68d66/node_modules/puppeteer-core');

const OUT = '/home/mamoun/ai/sadaqa/brag-output/composition/assets/captures';
const BASE = 'http://127.0.0.1:8099';
const CHROME = '/home/mamoun/.cache/puppeteer/chrome/linux-152.0.7977.54/chrome-linux64/chrome';

(async () => {
  const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: 'new',
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--force-color-profile=srgb', '--hide-scrollbars', '--lang=ar'],
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1280, height: 800, deviceScaleFactor: 2 });
  await page.evaluateOnNewDocument(() => { localStorage.setItem('theme', 'light'); });

  const shot = (n) => page.screenshot({ path: path.join(OUT, n) });
  const maskDomain = () => page.evaluate(() => {
    // present the product under its production domain for the recording
    document.body.innerHTML = document.body.innerHTML.split('127.0.0.1:8099').join('sadaqa.app');
  });

  // 1) hero
  await page.goto(BASE + '/', { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 1200));
  await maskDomain();
  await shot('home-hero.png');
  console.log('hero ok');

  // 2) form empty (scrolled to #create)
  await page.evaluate(() => { document.querySelector('#create').scrollIntoView({ behavior: 'instant', block: 'start' }); window.scrollBy(0, -64); });
  await new Promise(r => setTimeout(r, 500));
  await shot('form-empty.png');
  console.log('form-empty ok');

  // 3) form filled — real inputs
  await page.click('#typeRow .radio-pill:nth-child(1)');
  await page.click('#recipient_name');
  await page.type('#recipient_name', 'أمي', { delay: 30 });
  await page.click('#sender_name');
  await page.type('#sender_name', 'أحمد', { delay: 30 });
  await page.click('#message');
  await page.type('#message', 'رحمة ونور', { delay: 25 });
  await new Promise(r => setTimeout(r, 400));
  await shot('form-filled.png');
  console.log('form-filled ok');

  // 4) submit for real → success banner page
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 60000 }),
    page.click('.form-card button[type="submit"]'),
  ]);
  const token = new URL(page.url()).pathname.split('/').pop();
  console.log('submitted, token =', token);
  await new Promise(r => setTimeout(r, 1500));
  await maskDomain();
  await shot('success-banner.png');
  console.log('success-banner ok');

  // 5) click نسخ → toast
  await page.evaluate(() => { document.getElementById('shareCopy').click(); });
  await new Promise(r => setTimeout(r, 700));
  await shot('success-copied.png');
  console.log('success-copied ok');

  // 6) stream page (no ?new) — before play
  await page.goto(`${BASE}/streaming/${token}`, { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 2000));
  await maskDomain();
  await shot('stream-before.png');
  console.log('stream-before ok');

  // 7) press play for real → pause icon + live progress
  await page.click('#playBtn');
  await new Promise(r => setTimeout(r, 2200));
  await shot('stream-playing.png');
  console.log('stream-playing ok');

  // 8) phone viewport — same page, mobile layout
  await page.setViewport({ width: 390, height: 780, deviceScaleFactor: 3 });
  await page.goto(`${BASE}/streaming/${token}`, { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 1800));
  await page.evaluate(() => { document.getElementById('playBtn')?.click(); });
  await new Promise(r => setTimeout(r, 1800));
  await shot('stream-phone.png');
  console.log('stream-phone ok');

  await browser.close();
  console.log('ALL CAPTURES DONE, token=' + token);
})().catch(e => { console.error('CAPTURE FAILED:', e.message); process.exit(1); });
