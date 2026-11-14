/* Header notice disclosure; the original authored content remains the fallback. */
(() => {
  const initialise = () => {
    document.querySelectorAll('.zion-migration-announcement[data-zion-notice-key]').forEach((notice, index) => {
      if (notice.classList.contains('is-enhanced')) return;
      const key = notice.dataset.zionNoticeKey;
      let savedClosed = false;
      const read = () => {
        try {
          savedClosed = window.localStorage.getItem(key) === 'closed';
        } catch (_) { /* The control remains usable when storage is unavailable. */ }
      };
      read();

      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'zion-migration-announcement__toggle';
      button.innerHTML = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4M12 2V1"/></svg><span class="zion-migration-announcement__label">Notificări</span><span class="zion-migration-announcement__count" aria-hidden="true">1</span>';
      const panel = document.createElement('div');
      panel.id = `zion-notification-panel-${index + 1}`;
      panel.className = 'zion-migration-announcement__panel';
      const clip = document.createElement('div');
      clip.className = 'zion-migration-announcement__clip';
      const body = document.createElement('div');
      body.className = 'zion-migration-announcement__body';
      while (notice.firstChild) body.append(notice.firstChild);
      clip.append(body);
      panel.append(clip);
      button.setAttribute('aria-controls', panel.id);

      const sync = () => {
        const closed = savedClosed;
        notice.classList.toggle('is-collapsed', closed);
        button.setAttribute('aria-expanded', String(!closed));
        button.setAttribute('aria-label', closed ? 'Deschide notificarea despre migrare' : 'Restrânge notificarea despre migrare');
        panel.setAttribute('aria-hidden', String(closed));
        panel.inert = closed;
      };
      button.addEventListener('click', () => {
        savedClosed = !savedClosed;
        try { window.localStorage.setItem(key, savedClosed ? 'closed' : 'open'); } catch (_) { /* Optional persistence. */ }
        sync();
      });
      const refresh = () => { read(); sync(); };
      window.addEventListener('pageshow', refresh);
      window.addEventListener('storage', event => {
        if (event.key === key || event.key === null) refresh();
      });
      // The legacy HTML block has inline layout declarations; its copy and links stay intact.
      notice.style.removeProperty('display');
      notice.style.removeProperty('padding');
      notice.classList.add('is-enhanced');
      sync();
      notice.append(panel);
      const header = notice.closest('header')?.querySelector('.zion-header');
      const tools = header?.querySelector(':scope > .zion-header-tools');
      if (tools) {
        const cart = [...tools.children].find(element => element.classList.contains('zion-header-cart'));
        const account = [...tools.children].find(element => element.classList.contains('zion-header-account'));
        if (cart) tools.insertBefore(button, cart);
        else if (account) account.after(button);
        else tools.prepend(button);
        header.after(notice);
      } else notice.prepend(button);
    });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialise, { once: true });
  else initialise();
})();
