/* Part 3: success banner centered + fast toast capture. */
const path = require('path');
const puppeteer = require('/home/mamoun/.npm/_npx/110f701c48e68d66/node_modules/puppeteer-core');
const OUT = '/home/mamoun/ai/sadaqa/brag-output/composition/assets/captures';
const BASE = 'http://127.0.0.1:8099';
const CHROME = '/home/mamoun/.cache/puppeteer/chrome/linux-152.0.7977.54/chrome-linux64/chrome';
const TOKEN = '4848998';

(async () => {
  const browser = await puppeteer.launch({
    executablePath: CHROME, headless: 'new',
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--force-color-profile=srgb', '--hide-scrollbars', '--lang=ar'],
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1280, height: 800, deviceScaleFactor: 2 });
  await page.evaluateOnNewDocument(() => { localStorage.setItem('theme', 'light'); });
  const shot = (n) => page.screenshot({ path: path.join(OUT, n) });
  const maskDomain = () => page.evaluate(() => {
    document.body.innerHTML = document.body.innerHTML.split('127.0.0.1:8099').join('sadaqa.app');
  });

  await page.goto(`${BASE}/streaming/${TOKEN}?new=1`, { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 1600));
  // center the success banner in the viewport
  await page.evaluate(() => {
    const b = document.querySelector('.success-banner');
    b.scrollIntoView({ behavior: 'instant', block: 'center' });
  });
  await new Promise(r => setTimeout(r, 400));
  await maskDomain();
  await shot('success-banner2.png');
  console.log('success-banner2 ok');

  // toast attempt: click and screenshot fast (toast lifetime is short)
  await page.evaluate(() => { document.getElementById('shareCopy')?.click(); });
  await new Promise(r => setTimeout(r, 180));
  await maskDomain();
  await shot('success-copied2.png');
  console.log('success-copied2 ok');

  await browser.close();
  console.log('DONE');
})().catch(e => { console.error('FAILED:', e.message); process.exit(1); });
