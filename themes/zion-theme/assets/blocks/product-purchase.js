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
