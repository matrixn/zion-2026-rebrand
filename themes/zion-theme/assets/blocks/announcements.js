/* Mobile notice disclosure; the original authored content remains the fallback. */
(() => {
  const initialise = () => {
    document.querySelectorAll('.zion-migration-announcement[data-zion-notice-key]').forEach((notice, index) => {
      if (notice.classList.contains('is-enhanced')) return;
      const key = notice.dataset.zionNoticeKey;
      const mobile = window.matchMedia('(max-width: 767px)');
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
      button.innerHTML = '<span>Notificări</span><span class="zion-migration-announcement__count" aria-hidden="true">1</span><span class="zion-migration-announcement__chevron" aria-hidden="true">⌄</span>';
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
        const closed = mobile.matches && savedClosed;
        notice.classList.toggle('is-collapsed', closed);
        button.setAttribute('aria-expanded', String(!closed));
        button.setAttribute('aria-label', closed ? 'Deschide notificarea despre migrare' : 'Restrânge notificarea despre migrare');
        panel.setAttribute('aria-hidden', String(closed));
        panel.inert = closed;
      };
      button.addEventListener('click', () => {
        if (!mobile.matches) return;
        savedClosed = !savedClosed;
        try { window.localStorage.setItem(key, savedClosed ? 'closed' : 'open'); } catch (_) { /* Optional persistence. */ }
        sync();
      });
      const refresh = () => { read(); sync(); };
      mobile.addEventListener('change', refresh);
      window.addEventListener('pageshow', refresh);
      window.addEventListener('storage', event => {
        if (event.key === key || event.key === null) refresh();
      });
      // The legacy HTML block has inline layout declarations; its copy and links stay intact.
      notice.style.removeProperty('display');
      notice.style.removeProperty('padding');
      notice.classList.add('is-enhanced');
      sync();
      notice.append(button, panel);
    });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialise, { once: true });
  else initialise();
})();
