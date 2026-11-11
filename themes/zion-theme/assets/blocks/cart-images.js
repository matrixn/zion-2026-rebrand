/* The editorial layout needs the native full image, not an enlarged 80px thumbnail. */
(() => {
  const root = document.querySelector('.zion-cart--editorial');
  const store = window.wp?.data?.select('wc/store/cart');
  if (!root || !store?.getCartData) return;
  let queued = false;
  const observed = new WeakSet();
  const schedule = () => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => {
      queued = false;
      const images = new Map((store.getCartData()?.items || []).map(item => [item.key, item.images?.[0]]));
      for (const row of root.querySelectorAll('[data-cart-item-key]')) {
        const image = row.querySelector('.wc-block-cart-item__image img');
        const source = images.get(row.dataset.cartItemKey);
        if (!image || !source?.src) continue;
        if (!observed.has(image)) { resize.observe(image); observed.add(image); }
        const attributes = { src: source.src, srcset: source.srcset || '', sizes: `${Math.max(1, Math.ceil(image.getBoundingClientRect().width))}px` };
        for (const [key, value] of Object.entries(attributes)) {
          if (image.getAttribute(key) !== value) image.setAttribute(key, value);
        }
      }
    });
  };
  const resize = new ResizeObserver(schedule);
  new MutationObserver(schedule).observe(root, { childList: true, subtree: true, attributes: true, attributeFilter: ['src', 'srcset'] });
  window.wp.data.subscribe(schedule, 'wc/store/cart');
  schedule();
})();
