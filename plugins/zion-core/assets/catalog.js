import { actions, state } from '@wordpress/interactivity-router';

const catalog = '.zion-catalog-native';
const more = '.zion-catalog-more a';
const sortForm = '.zion-catalog-toolbar form.woocommerce-ordering';
let loading = false;
let navigation = 0;
let observed;
let noticeTimeout;

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
  try {
    await actions.navigate(url, { replace });
    if (current === navigation && message && location.href === url) announce(message);
  } finally {
    if (current === navigation) {
      loading = false;
      observeMore();
    }
  }
}

const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
  for (const entry of entries) {
    if (entry.isIntersecting && !loading && !state.navigating) {
      observer.unobserve(entry.target);
      observed = undefined;
      navigate(entry.target.href, '', true);
    }
  }
}) : null;

function observeMore() {
  const next = document.querySelector(`${catalog} [data-zion-load="auto"] a`);
  if (observed !== next) {
    if (observed) observer?.unobserve(observed);
    if (next) observer?.observe(next);
    observed = next;
  }
}

function sort(form) {
  const url = new URL(form.getAttribute('action') || location.pathname, location.href);
  const fields = new URLSearchParams(new FormData(form));
  for (const name of new Set(fields.keys())) url.searchParams.delete(name);
  for (const [name, value] of fields) url.searchParams.append(name, value);
  navigate(url.href, 'Sortarea a fost actualizată.');
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
  const link = event.target.closest(`${catalog} ${more}, ${catalog} .zion-catalog-reset, ${catalog} .zion-catalog-sale a`);
  if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
  event.preventDefault();
  navigate(link.href, '');
});

new MutationObserver(observeMore).observe(document.body, { childList: true, subtree: true });
observeMore();
