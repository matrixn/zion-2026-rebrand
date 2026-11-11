/* Decorate native labels without inserting nodes into WooCommerce's reactive tree. */
(() => {
  let pending = false;
  function update() {
    pending = false;
    for (const root of document.querySelectorAll('[data-zion-category-prefix]')) {
      let count = 0;
      const selected = Boolean(new URL(location.href).searchParams.get('categories'));
      for (const item of root.querySelectorAll('.wc-block-product-filter-removable-chips__item')) {
        const label = item.querySelector('.wc-block-product-filter-removable-chips__label');
        if (!label) continue;
        const text = label.textContent.trim();
        const category = text.startsWith(root.dataset.zionCategoryPrefix);
        if (category) {
          const short = text.slice(root.dataset.zionCategoryPrefix.length);
          if (label.dataset.zionShortLabel !== short) label.dataset.zionShortLabel = short;
          if (selected) count++;
        }
        if (item.classList.contains('zion-category-chip-inactive') !== (category && !selected)) item.classList.toggle('zion-category-chip-inactive', category && !selected);
        if (item.classList.contains('zion-category-chip') !== category) item.classList.toggle('zion-category-chip', category);
      }
      if (root.classList.contains('zion-category-group') !== (count > 0)) root.classList.toggle('zion-category-group', count > 0);
    }
  }
  new MutationObserver(() => {
    if (!pending) { pending = true; requestAnimationFrame(update); }
  }).observe(document.body, { childList: true, subtree: true, characterData: true });
  addEventListener('popstate', () => requestAnimationFrame(update));
  update();
})();
