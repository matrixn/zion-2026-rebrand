(() => {
  const badges = document.querySelectorAll('.zion-header-cart-count');
  const store = window.wc?.wcBlocksData?.cartStore;
  if (!badges.length || !store || !window.wp?.data) return;

  const { select, dispatch, subscribe } = window.wp.data;
  const cart = select(store);
  let previous;
  const render = () => {
    const { itemsCount } = cart.getCartData();
    if (!cart.hasFinishedResolution('getCartData') || !Number.isSafeInteger(itemsCount) || itemsCount < 0 || itemsCount === previous) return;
    previous = itemsCount;
    for (const badge of badges) {
      badge.textContent = String(itemsCount);
      badge.hidden = false;
      badge.closest('a').setAttribute('aria-label', `Coș, ${itemsCount} ${itemsCount === 1 ? 'produs' : 'produse'}`);
    }
  };
  subscribe(render, store);
  render();

  // Coalesce the classic and native events for the same successful mutation.
  let queued = false;
  const refresh = () => {
    if (queued) return;
    queued = true;
    queueMicrotask(() => {
      queued = false;
      dispatch(store).invalidateResolutionForStoreSelector('getCartData');
      render();
    });
  };
  document.body.addEventListener('wc-blocks_added_to_cart', refresh);
  document.body.addEventListener('wc-blocks_removed_from_cart', refresh);
  window.jQuery(document.body).on('added_to_cart.zionCartCount removed_from_cart.zionCartCount updated_wc_div.zionCartCount', refresh);
  window.addEventListener('pageshow', event => { if (event.persisted) refresh(); });
})();
