(() => {
  document.querySelectorAll('nav.zion-navigation').forEach(nav => {
    const overlay = nav.querySelector('.wp-block-navigation__responsive-container');
    const header = nav.closest('.zion-header');
    const tools = header?.querySelector(':scope > .zion-header-tools');
    const mobile = matchMedia('(max-width:1100px)');
    if (tools) {
      const origin = document.createComment('Zion desktop navigation position');
      nav.before(origin);
      const position = () => { if(mobile.matches) tools.after(nav); else origin.after(nav); };
      mobile.addEventListener('change',position);
      position();
    }
    const path = value => decodeURI(value).replace(/\/+$/, '') || '/';
    const markCurrent = () => {
      nav.querySelectorAll('.zion-nav-current,.zion-nav-ancestor').forEach(item => item.classList.remove('zion-nav-current', 'zion-nav-ancestor'));
      nav.querySelectorAll('a.wp-block-navigation-item__content').forEach(link => {
        let current = false;
        try {
          const href = link.getAttribute('href');
          const url = href ? new URL(href, location.href) : null;
          current = url && url.origin === location.origin && !url.hash && path(url.pathname) === path(location.pathname);
        } catch { /* An edited non-HTTP link is not the current document. */ }
        if (current) {
          link.setAttribute('aria-current', 'page');
          link.classList.add('zion-nav-current');
          let parent = link.closest('li');
          while (parent && nav.contains(parent)) {
            parent.classList.add('zion-nav-ancestor');
            parent = parent.parentElement?.closest('li');
          }
        } else if (link.getAttribute('aria-current') === 'page') link.removeAttribute('aria-current');
      });
    };
    markCurrent();
    window.addEventListener('pageshow', markCurrent);
    window.addEventListener('popstate', markCurrent);

    // WordPress owns desktop disclosure, modal opening, Escape and scroll locking.
    // In its overlay core expands every submenu; add accessible mobile accordions.
    const setExpanded = (button, expanded) => {
      const submenu = button.parentElement.querySelector(':scope > .wp-block-navigation__submenu-container');
      if (!submenu) return;
      button.setAttribute('aria-expanded', String(expanded));
      submenu.hidden = !expanded;
    };
    let wasOpen = false;
    const expandCurrentPath = () => {
      const open = overlay?.classList.contains('is-menu-open');
      if (open && !wasOpen) {
        nav.querySelectorAll('li > button.wp-block-navigation-submenu__toggle').forEach(button => setExpanded(button, button.parentElement.classList.contains('zion-nav-ancestor')));
      } else if (!open && wasOpen) {
        nav.querySelectorAll('.wp-block-navigation__submenu-container[hidden]').forEach(submenu => { submenu.hidden = false; });
      }
      wasOpen = open;
    };
    if (overlay) new MutationObserver(expandCurrentPath).observe(overlay, { attributes: true, attributeFilter: ['class'] });
    nav.addEventListener('click', event => {
      const button = event.target.closest('button.wp-block-navigation-submenu__toggle');
      if (!button || !overlay?.classList.contains('is-menu-open')) return;
      event.preventDefault();
      event.stopImmediatePropagation();
      setExpanded(button, button.getAttribute('aria-expanded') !== 'true');
    }, true);
    nav.addEventListener('keydown', event => {
      if (event.key !== 'Tab' || !overlay?.classList.contains('is-menu-open')) return;
      const focusable = [...overlay.querySelectorAll('a[href],button,input,[tabindex="0"]')].filter(item => item.getClientRects().length && getComputedStyle(item).visibility !== 'hidden' && !item.disabled);
      const first = focusable[0], last = focusable.at(-1);
      if ((event.shiftKey && document.activeElement === first) || (!event.shiftKey && document.activeElement === last)) {
        event.preventDefault();
        event.stopImmediatePropagation();
        (event.shiftKey ? last : first)?.focus();
      }
    }, true);
    matchMedia('(min-width: 1101px)').addEventListener('change', event => {
      if (event.matches && overlay?.classList.contains('is-menu-open') && !nav.querySelector('.wp-block-navigation__responsive-container-open.always-shown')) nav.querySelector('.wp-block-navigation__responsive-container-close')?.click();
    });
  });
})();
