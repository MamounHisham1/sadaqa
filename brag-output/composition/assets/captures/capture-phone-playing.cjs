/* Capture the phone stream page in the PLAYING state. */
const path = require('path');
const puppeteer = require('/home/mamoun/.npm/_npx/110f701c48e68d66/node_modules/puppeteer-core');
const OUT = '/home/mamoun/ai/sadaqa/brag-output/composition/assets/captures';
const BASE = 'http://127.0.0.1:8099';
const CHROME = '/home/mamoun/.cache/puppeteer/chrome/linux-152.0.7977.54/chrome-linux64/chrome';
const TOKEN = '4848998';

(async () => {
  const browser = await puppeteer.launch({
    executablePath: CHROME, headless: 'new',
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--force-color-profile=srgb', '--hide-scrollbars', '--lang=ar', '--autoplay-policy=no-user-gesture-required'],
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 390, height: 780, deviceScaleFactor: 3 });
  await page.evaluateOnNewDocument(() => { localStorage.setItem('theme', 'light'); });

  await page.goto(`${BASE}/streaming/${TOKEN}`, { waitUntil: 'domcontentloaded' });
  await new Promise(r => setTimeout(r, 2000));
  await page.click('#playBtn');
  await new Promise(r => setTimeout(r, 2500));
  const state = await page.evaluate(() => {
    const pause = document.querySelector('#play-btn .i-pause, #playBtn .i-pause');
    return { pauseVisible: pause ? getComputedStyle(pause).opacity : 'none' };
  });
  console.log('player state:', JSON.stringify(state));
  await page.evaluate(() => {
    document.body.innerHTML = document.body.innerHTML.split('127.0.0.1:8099').join('sadaqa.app');
  });
  await page.screenshot({ path: path.join(OUT, 'stream-phone-playing.png') });
  console.log('stream-phone-playing saved');
  await browser.close();
})().catch(e => { console.error('FAILED:', e.message); process.exit(1); });
