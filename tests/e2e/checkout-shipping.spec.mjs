import { test, expect } from '@playwright/test';

test('delivery selection badges follow native rates and the same-address default stays optional', async ({ page, browser, baseURL }) => {
  test.setTimeout(120_000);
  const site = new URL(baseURL);
  test.skip(site.port !== '8917' || !['localhost', '127.0.0.1', '[::1]'].includes(site.hostname), 'Synthetic shipping fixtures run only on wp-env tests.');
  await page.goto('/wp-login.php');
  const login = await page.request.post('/wp-login.php', { maxRedirects: 0, form: { log: 'admin', pwd: 'password', testcookie: '1' } });
  expect(login.status()).toBe(302);
  const nonce = await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const wc = async (method, path, data) => {
    const response = await page.request.fetch('/wp-json/wc/v3/' + path, { method, headers: { 'X-WP-Nonce': nonce }, ...(data ? { data } : {}) });
    expect(response.ok(), await response.text()).toBe(true);
    return response.json();
  };
  const cleanup = [];
  const guest = await browser.newContext({ baseURL });
  try {
    const cod = await wc('GET', 'payment_gateways/cod');
    cleanup.push(() => wc('PUT', 'payment_gateways/cod', { enabled: cod.enabled }));
    await wc('PUT', 'payment_gateways/cod', { enabled: true });
    const destination = await wc('GET', 'settings/shipping/woocommerce_ship_to_destination');
    cleanup.push(() => wc('PUT', 'settings/shipping/woocommerce_ship_to_destination', { value: destination.value }));
    await wc('PUT', 'settings/shipping/woocommerce_ship_to_destination', { value: 'billing' });
    const zone = await wc('POST', 'shipping/zones', { name: 'Zion selection test', order: 0 });
    cleanup.push(() => wc('DELETE', `shipping/zones/${zone.id}?force=true`));
    await wc('PUT', `shipping/zones/${zone.id}/locations`, [{ code: 'RO', type: 'country' }]);
    for (const [title, cost] of [['Livrare Zion test', '8'], ['Ridicare Zion test', '10']]) {
      await wc('POST', `shipping/zones/${zone.id}/methods`, { method_id: 'flat_rate', enabled: true, settings: { title, cost, tax_status: 'none' } });
    }
    const [product] = await wc('GET', 'products?sku=ZION-DEMO-3D-01');
    const customer = await guest.newPage();
    await customer.goto('/?add-to-cart=' + product.id);
    await customer.goto('/checkout/');
    const sameAddress = customer.getByRole('checkbox', { name: 'Folosește aceeași adresă pentru facturare', exact: true });
    await expect(sameAddress).toBeChecked();
    await customer.locator('#shipping-country').selectOption('RO');
    const shipping = customer.locator('.wp-block-woocommerce-checkout-shipping-methods-block');
    const delivery = shipping.getByRole('radio', { name: /Livrare Zion test/ });
    const collection = shipping.getByRole('radio', { name: /Ridicare Zion test/ });
    const expectShipping = amount => expect.poll(async () => {
      const cart = await (await guest.request.get('/wp-json/wc/store/v1/cart')).json();
      return Number(cart.totals.total_shipping);
    }, { timeout: 15_000 }).toBe(amount);
    await expect(delivery).toBeVisible();
    await collection.check();
    await expect(collection).toBeChecked();
    const selected = shipping.locator('.wc-block-components-radio-control__option-checked');
    await expect(selected).toContainText('Ridicare Zion test');
    expect(await selected.evaluate(e => getComputedStyle(e, '::after').content)).toContain('Selectat');
    await expectShipping(1000);
    await sameAddress.uncheck();
    await expect(customer.locator('#billing-first_name')).toBeVisible();
    await delivery.check();
    await expect(sameAddress).not.toBeChecked();
    await expect(selected).toContainText('Livrare Zion test');
    await expectShipping(800);
    await sameAddress.check();
    await expect(customer.locator('#billing-first_name')).toBeHidden();
    await delivery.focus();
    await customer.keyboard.press('ArrowDown');
    await expectShipping(1000);
    await expect(selected).toContainText('Ridicare Zion test');
    await customer.keyboard.press('ArrowUp');
    await expectShipping(800);
    await expect(delivery).toBeChecked();
    for (const width of [390, 768, 1024, 1440]) {
      await customer.setViewportSize({ width, height: 1000 });
      expect(await customer.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
      expect(await selected.evaluate(e => getComputedStyle(e, '::after').content)).toContain('Selectat');
      const offset = await selected.evaluate(e => {
        const radio = e.querySelector('input').getBoundingClientRect();
        const label = e.querySelector('.wc-block-components-radio-control__option-layout').getBoundingClientRect();
        return Math.abs(radio.y + radio.height / 2 - label.y - label.height / 2);
      });
      expect(offset).toBeLessThanOrEqual(2);
      await shipping.screenshot({ path: `build/checkout-shipping-${width}-1.1.36.png` });
    }
    await expectShipping(800);
    await expect(delivery).toBeChecked();
  } finally {
    await guest.close();
    const failures = [];
    for (const restore of cleanup.reverse()) { try { await restore(); } catch (error) { failures.push(error); } }
    expect(failures).toEqual([]);
  }
});
