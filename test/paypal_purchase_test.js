const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({ headless: false, defaultViewport: { width: 900, height: 600 } });
  const page = await browser.newPage();

  // Navigate to the local site
  await page.goto('http://localhost/nuevo%20proyecto%20coff%202.0/index.php');

  // Wait for the page to load and the add to cart buttons to be available
  await page.waitForSelector('.add-cart');

  // Click the first add to cart button
  await page.click('.add-cart');

  // Open the cart modal
  await page.waitForSelector('#cart-toggle-icon');
  await page.click('#cart-toggle-icon');

  // Wait for the PayPal button container to be visible
  await page.waitForSelector('#paypal-button-container', { visible: true });

  // Note: Automating PayPal payment flow is complex due to security and iframe restrictions.
  // Here we simulate clicking the PayPal button to test if it triggers without errors.
  // For full end-to-end testing, manual testing or PayPal sandbox environment is recommended.

  // Click the PayPal button (it is rendered inside an iframe)
  const frames = await page.frames();
  const paypalFrame = frames.find(frame => frame.url().includes('paypal.com'));
  if (paypalFrame) {
    // Try to click inside the PayPal iframe (may be restricted)
    try {
      const paypalButton = await paypalFrame.$('button');
      if (paypalButton) {
        await paypalButton.click();
        console.log('Clicked PayPal button inside iframe.');
      } else {
        console.log('PayPal button not found inside iframe.');
      }
    } catch (e) {
      console.log('Could not click PayPal button inside iframe due to cross-origin restrictions.');
    }
  } else {
    console.log('PayPal iframe not found.');
  }

  // Close browser after short delay
  await page.waitForTimeout(5000);
  await browser.close();
})();
