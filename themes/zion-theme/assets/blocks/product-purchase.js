(() => {
	const purchase = document.querySelector('#zion-purchase');
	const form = purchase?.querySelector('form.cart');
	const native = form?.querySelector('.single_add_to_cart_button');
	if (!native) return;

	const variation = form.querySelector('input.variation_id');
	const buyNow = form.querySelector('.zion-buy-now');
	const sticky = document.querySelector('[data-zion-sticky]');
	const ready = () => !native.disabled && !native.classList.contains('disabled') &&
		(!variation || Number(variation.value) > 0);

	const sync = () => {
		if (buyNow) buyNow.disabled = !ready();
		if (sticky) sticky.textContent = ready() ? sticky.dataset.ready : sticky.dataset.unready;
	};
	new MutationObserver(sync).observe(native, { attributes: true, attributeFilter: ['class', 'disabled'] });
	if (variation) new MutationObserver(sync).observe(variation, { attributes: true, attributeFilter: ['value'] });
	form.addEventListener('change', sync);
	sync();

	sticky?.addEventListener('click', (event) => {
		if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
		event.preventDefault();
		if (ready()) {
			native.click();
		} else {
			purchase.scrollIntoView({ block: 'center' });
			form.querySelector('select')?.focus({ preventScroll: true });
		}
	});
})();

document.addEventListener('submit', async event => {
  const form = event.target;
  const notice = form.closest('#zion-purchase')?.querySelector('.zion-purchase-notice');
  const button = event.submitter;
  if (!notice || !form.matches('form.cart') || button?.hasAttribute('formaction') || event.defaultPrevented) return;

  const action = new URL(form.action);
  if (action.origin !== location.origin || form.method.toLowerCase() !== 'post') return;
  event.preventDefault();
  if (form.getAttribute('aria-busy') === 'true') return;

  const fields = new FormData(form, button ?? undefined);
  const disabled = button?.disabled;
  form.setAttribute('aria-busy', 'true');
  if (button) button.disabled = true;
  const finish = window.zionCommercePending?.();
  notice.textContent = 'Se adaugă în coș…';

  try {
    const response = await fetch(action, { method: 'POST', body: fields, credentials: 'same-origin' });
    if (!response.ok) throw new Error('WooCommerce request failed');
    if (response.redirected && new URL(response.url).pathname !== action.pathname) {
      location.assign(response.url);
      return;
    }

    const result = new DOMParser().parseFromString(await response.text(), 'text/html');
    const messages = [...result.querySelectorAll('.woocommerce-error, .woocommerce-message, .woocommerce-info, .wc-block-components-notice-banner')];
    notice.replaceChildren(...messages);
    if (!result.querySelector('.woocommerce-error, .wc-block-components-notice-banner.is-error')) {
      if (!notice.textContent) notice.textContent = 'Produsul a fost adăugat în coș.';
      document.body.dispatchEvent(new CustomEvent('wc-blocks_added_to_cart', {
        bubbles: true,
        detail: { preserveCartData: false },
      }));
    }
  } catch {
    notice.textContent = 'Nu am putut confirma adăugarea. Verifică coșul înainte de a încerca din nou.';
  } finally {
    finish?.();
    form.removeAttribute('aria-busy');
    if (button) button.disabled = disabled;
  }
});
