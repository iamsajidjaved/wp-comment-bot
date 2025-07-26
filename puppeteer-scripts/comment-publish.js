const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

(async () => {
  try {
    const filePath = process.argv[2];
    if (!filePath) throw new Error('JSON input file path is required.');

    const rawData = fs.readFileSync(filePath, 'utf-8');
    const input = JSON.parse(rawData);
    const { postUrl, comment, email, author } = input;

    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    await page.goto(postUrl, { waitUntil: 'networkidle2' });

    // Screenshot feature removed as per user request

    // Wait for the comment textarea
    await page.waitForSelector('textarea#comment', { timeout: 15000 });

    // Type the comment form inputs
    await page.type('textarea#comment', comment);
    await page.type('input#author', author);
    await page.type('input#email', email);

    // Submit the comment form and wait for page navigation
    await Promise.all([
      page.click('input#submit'),
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
    ]);

    // Extract the comment ID from the URL query parameters
    const currentUrl = page.url();
    const urlObj = new URL(currentUrl);
    const commentId = urlObj.searchParams.get('unapproved');

    await browser.close();

    if (commentId) {
      console.log(JSON.stringify({ commentId }));
    } else {
      console.log(JSON.stringify({ error: 'Comment ID not found in URL' }));
    }
  } catch (err) {
    console.log(JSON.stringify({ error: err.message }));
    process.exit(1);
  }
})();
