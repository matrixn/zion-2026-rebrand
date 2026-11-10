(() => {
  const indicator = document.createElement('div');
  indicator.className = 'zion-commerce-progress';
  indicator.setAttribute('role', 'status');
  document.body.append(indicator);

  let requests = 0;
  window.zionCommercePending = () => {
    if (++requests === 1) indicator.textContent = 'Se încarcă…';
    let finished = false;
    return () => {
      if (finished) return;
      finished = true;
      if (--requests === 0) indicator.textContent = '';
    };
  };

  window.wp?.apiFetch?.use(async (options, next) => {
    if (!/\/wc\/store\//.test(options.path || options.url || '')) return next(options);
    const done = window.zionCommercePending();
    try {
      return await next(options);
    } finally {
      done();
    }
  });

  const pending = new WeakMap();
  window.jQuery(document).on('ajaxSend.zionCommerce', (_event, request, settings) => {
    if (/(?:[?&]wc-ajax=|\/wc-ajax\/|\/wc\/store\/)/.test(settings.url)) {
      pending.set(request, window.zionCommercePending());
    }
  }).on('ajaxComplete.zionCommerce', (_event, request) => {
    pending.get(request)?.();
    pending.delete(request);
  });
})();
const gallery = document.querySelector('.zion-product-media .woocommerce-product-gallery');
if (gallery) {
  const thumbSelector = '.flex-control-thumbs img';
  const updateThumbs = () => gallery.querySelectorAll(thumbSelector).forEach((image, index) => {
    image.tabIndex = 0;
    image.setAttribute('role', 'button');
    image.setAttribute('aria-label', `Afișează imaginea ${index + 1}${image.alt ? ': ' + image.alt : ''}`);
    image.setAttribute('aria-pressed', String(image.classList.contains('flex-active')));
  });
  new MutationObserver(updateThumbs).observe(gallery, { subtree: true, childList: true, attributes: true, attributeFilter: ['class'] });
  gallery.addEventListener('keydown', event => {
    if (event.target.matches(thumbSelector) && ['Enter', ' '].includes(event.key)) {
      event.preventDefault();
      event.target.click();
    }
  });
  updateThumbs();
}
