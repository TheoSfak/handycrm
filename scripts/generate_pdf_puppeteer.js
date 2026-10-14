const path = require('path');
// Ensure node_modules from project root is resolvable
module.paths.push(path.join(__dirname, '..', 'node_modules'));

const puppeteer = require('puppeteer-core');
const fs = require('fs');
const os = require('os');

const CHROME_PATHS = [
  process.env.CHROME_BIN,
  process.env.PUPPETEER_EXECUTABLE_PATH,
  // Linux standard paths
  '/usr/bin/google-chrome-stable',
  '/usr/bin/google-chrome',
  '/usr/bin/chromium',
  '/usr/bin/chromium-browser',
  '/snap/bin/chromium',
  // macOS standard paths
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
  '/Applications/Chromium.app/Contents/MacOS/Chromium',
  // Windows standard paths
  'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
  'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
  'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
  'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
].filter(Boolean);

function findBrowser() {
  for (const p of CHROME_PATHS) {
    if (fs.existsSync(p)) return p;
  }
  throw new Error('Neither Chrome nor Edge was found on this system');
}

async function main() {
  let html = '';
  process.stdin.setEncoding('utf8');

  for await (const chunk of process.stdin) {
    html += chunk;
  }

  if (!html.trim()) {
    console.error('Error: Empty HTML input');
    process.exit(1);
  }

  const executablePath = findBrowser();
  const tempProfileDir = path.join(os.tmpdir(), 'puppeteer_profile_' + Math.random().toString(36).substring(2, 9));

  const browser = await puppeteer.launch({
    executablePath,
    headless: 'new',
    userDataDir: tempProfileDir,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-gpu',
      '--disable-dev-shm-usage',
      '--run-all-compositor-stages-before-draw',
    ],
  });

  try {
    const page = await browser.newPage();
    
    // Set in-memory HTML
    await page.setContent(html, {
      waitUntil: 'networkidle0',
      timeout: 30000,
    });

    // Ensure fonts are loaded
    await page.evaluateHandle('document.fonts.ready');

    const pdfBuffer = await page.pdf({
      format: 'A4',
      printBackground: true,
      margin: { top: 0, right: 0, bottom: 0, left: 0 },
      preferCSSPageSize: true,
    });

    process.stdout.write(pdfBuffer);
  } finally {
    await browser.close();
    // Clean up temporary profile
    try {
      if (fs.existsSync(tempProfileDir)) {
        fs.rmSync(tempProfileDir, { recursive: true, force: true });
      }
    } catch (_) {}
  }
}

main().catch(err => {
  console.error(err && err.message ? err.message : err);
  process.exit(1);
});
