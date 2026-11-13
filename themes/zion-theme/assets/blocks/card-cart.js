(() => {
  const store = window.wc?.wcBlocksData?.cartStore;
  if (!store || !window.wp?.data) return;
  const {select,dispatch,subscribe} = window.wp.data;
  const cart = select(store);
  const productPath = value => {
    try { const url = new URL(value, location.href); return url.origin === location.origin ? url.pathname.replace(/\/+$/, '') : ''; }
    catch { return ''; }
  };
  const render = () => {
    const {items} = cart.getCartData();
    if (!cart.hasFinishedResolution('getCartData') || !Array.isArray(items)) return;
    for (const card of document.querySelectorAll('.zion-product-card[data-zion-product]')) {
      const link = card.querySelector('.zion-card-cart-hop');
      if (!link) continue;
      const path = productPath(card.dataset.zionProductUrl);
      const quantity = items.reduce((sum,item) => {
        // Variations share the parent's canonical product URL, not its ID.
        const matches = String(item.id) === card.dataset.zionProduct || path && path === productPath(item.permalink);
        return sum + (matches && Number.isSafeInteger(item.quantity) ? item.quantity : 0);
      },0);
      const hidden = quantity <= 0;
      if (link.hidden !== hidden) link.hidden = hidden;
      const count = link.querySelector('.zion-cart-hop-count');
      if (count.textContent !== String(quantity)) count.textContent = String(quantity);
      const label = `Vezi coșul: ${quantity} × ${card.dataset.zionProductName}`;
      if (link.getAttribute('aria-label') !== label) link.setAttribute('aria-label',label);
    }
  };
  let scheduled = false;
  const schedule = () => {
    if (scheduled) return;
    scheduled = true;
    queueMicrotask(() => { scheduled = false; render(); });
  };
  subscribe(schedule,store);
  new MutationObserver(schedule).observe(document.body,{childList:true,subtree:true});
  let refreshing = false;
  const refresh = () => {
    if (refreshing) return;
    refreshing = true;
    queueMicrotask(() => { refreshing = false; dispatch(store).invalidateResolutionForStoreSelector('getCartData'); schedule(); });
  };
  // The existing header counter already bridges these events to the same store.
  if (!document.querySelector('.zion-header-cart-count')) {
    document.body.addEventListener('wc-blocks_added_to_cart',refresh);
    document.body.addEventListener('wc-blocks_removed_from_cart',refresh);
    window.jQuery(document.body).on('added_to_cart.zionCard removed_from_cart.zionCard updated_wc_div.zionCard',refresh);
  }
  window.addEventListener('pageshow',event => { if(event.persisted) refresh(); });
  render();
})();
