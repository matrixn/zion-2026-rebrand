import { watch } from '@wordpress/interactivity';
import { actions, state } from '@wordpress/interactivity-router';

const catalog = '.zion-catalog-native';
const sortForm = '.zion-catalog-toolbar form.woocommerce-ordering';
let loading = false;
let navigation = 0;
let observed;
let noticeTimeout;
let waitForScroll = false;

const notice = document.createElement('div');
notice.className = 'zion-shop-notice';
notice.setAttribute('role', 'status');
document.body.append(notice);

function announce(message) {
  clearTimeout(noticeTimeout);
  notice.textContent = message;
  noticeTimeout = setTimeout(() => { notice.textContent = ''; }, 2400);
}

async function navigate(url, message, replace = false) {
  if (url === location.href && !loading) return;
  const current = ++navigation;
  loading = true;
  const finish = window.zionCommercePending?.();
  try {
    await actions.navigate(url, { replace });
    if (current === navigation && message && location.href === url) announce(message);
  } finally {
    finish?.();
    if (current === navigation) {
      loading = false;
      observeMore();
    }
  }
}

let finishRouter;
let routerPending = false;
watch(() => {
  routerPending = state.navigating ?? (state.navigation?.hasStarted && !state.navigation?.hasFinished);
  if (routerPending) {
    finishRouter ??= window.zionCommercePending?.();
  } else {
    finishRouter?.();
    finishRouter = undefined;
  }
});

const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
  for (const entry of entries) {
    if (entry.target === observed && entry.isIntersecting && !waitForScroll && !loading && !routerPending) {
      observer.unobserve(entry.target);
      observed = undefined;
      navigate(entry.target.dataset.zionNext, '', true).catch(() => {});
    }
  }
}) : null;

function observeMore() {
  const next = document.querySelector(`${catalog} [data-zion-next]`);
  if (observed !== next) {
    if (observed) observer?.unobserve(observed);
    if (next) observer?.observe(next);
    observed = next;
  }
}

// The browser can restore a position next to the sentinel during Back/Forward.
// Keep that history entry intact until the visitor scrolls again.
window.addEventListener('popstate', () => { waitForScroll = true; });
function resumeMore() {
  if (!waitForScroll) return;
  waitForScroll = false;
  if (observed) observer?.unobserve(observed);
  observed = undefined;
  observeMore();
}
window.addEventListener('wheel', resumeMore, { passive: true });
window.addEventListener('touchmove', resumeMore, { passive: true });
window.addEventListener('pointerdown', event => {
  if (event.target === document.documentElement) resumeMore();
}, { passive: true });
window.addEventListener('keydown', event => {
  if (['ArrowDown', 'PageDown', 'End', ' '].includes(event.key) &&
      !event.target.closest('input, select, textarea, button, [contenteditable="true"]')) resumeMore();
});

function sort(form) {
  const url = new URL(form.getAttribute('action') || location.pathname, location.href);
  const fields = new URLSearchParams(new FormData(form));
  for (const name of new Set(fields.keys())) url.searchParams.delete(name);
  for (const [name, value] of fields) url.searchParams.append(name, value);
  navigate(url.href, 'Sortarea a fost actualizată.').catch(() => location.assign(url.href));
}

document.addEventListener('change', event => {
  const select = event.target.closest(`${sortForm} select[name="orderby"]`);
  if (!select) return;
  // WooCommerce’s legacy archive listener otherwise submits the same form.
  event.stopPropagation();
  sort(select.form);
}, true);

document.addEventListener('submit', event => {
  if (!event.target.matches(sortForm)) return;
  event.preventDefault();
  sort(event.target);
});

document.addEventListener('click', event => {
  const link = event.target.closest(`${catalog} .zion-catalog-reset, ${catalog} .zion-catalog-sale a`);
  if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
  event.preventDefault();
  navigate(link.href, '').catch(() => location.assign(link.href));
});

new MutationObserver(observeMore).observe(document.body, { childList: true, subtree: true });
observeMore();
