/* Progressive header news. Native links remain the source of truth. */
(() => {
  const initialise = () => document.querySelectorAll('.zion-updates:not(.is-enhanced)').forEach((notice, index) => {
    const panel = notice.querySelector('.zion-updates__panel');
    const items = [...notice.querySelectorAll('.zion-updates__items > li')];
    if (!panel || !items.length) return;
    const controls = notice.querySelector('.zion-updates__controls');
    const pause = notice.querySelector('.zion-updates__pause');
    const count = notice.querySelector('.zion-updates__count');
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    const key = 'zion-header-updates-v1';
    const read = () => { try { return localStorage.getItem(key) !== 'closed'; } catch { return true; } };
    let opened = read(), paused = false, hovered = false, focused = false;
    let current = 0, cursor = 0, phase = 'typing', timer;
    const segmenter = typeof Intl.Segmenter === 'function' ? new Intl.Segmenter('ro', { granularity: 'grapheme' }) : null;
    const entries = items.map(item => {
      const link = item.querySelector('a');
      const title = item.querySelector('.zion-updates__title');
      const text = title.textContent;
      link.setAttribute('aria-label', `${item.querySelector('.zion-updates__category').textContent}: ${text}`);
      title.setAttribute('aria-hidden', 'true');
      const typed = document.createElement('span');
      typed.className = 'zion-updates__typed';
      const characters = document.createTextNode('');
      const caret = document.createElement('span');
      caret.className = 'zion-updates__caret';
      caret.setAttribute('aria-hidden', 'true');
      typed.append(characters, caret);
      title.append(typed);
      return { item, characters, text, letters: segmenter ? [...segmenter.segment(text)].map(letter => letter.segment) : Array.from(text) };
    });
    panel.id = `zion-updates-panel-${index + 1}`;
    const bell = document.createElement('button');
    bell.type = 'button';
    bell.className = 'zion-updates__toggle';
    bell.setAttribute('aria-controls', panel.id);
    bell.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>';
    const badge = document.createElement('span');
    badge.className = 'zion-updates__badge';
    badge.textContent = String(items.length);
    badge.setAttribute('aria-hidden', 'true');
    bell.append(badge);
    const header = notice.closest('header')?.querySelector('.zion-header') || document.querySelector('.zion-header');
    const tools = header?.querySelector('.zion-header-tools');
    if (header) header.before(notice);
    if (tools) {
      const cart = tools.querySelector('.zion-header-cart');
      if (cart) cart.before(bell);
      else tools.append(bell);
    } else notice.before(bell);
    const stop = () => { clearTimeout(timer); timer = undefined; };
    const canRun = () => opened && !paused && !motion.matches && !hovered && !focused && !document.hidden && entries.length > 1;
    const schedule = delay => { stop(); if (canRun()) timer = setTimeout(step, delay); };
    const show = () => {
      entries.forEach((entry, i) => {
        entry.item.classList.toggle('is-current', i === current);
        entry.item.setAttribute('aria-hidden', String(i !== current));
        entry.item.inert = i !== current;
      });
      count.textContent = `${current + 1} / ${entries.length}`;
      notice.dataset.current = String(current + 1);
      notice.classList.remove('is-leaving');
      notice.classList.toggle('is-typing', phase === 'typing');
    };
    const complete = () => {
      notice.classList.remove('is-leaving', 'is-typing');
      cursor = entries[current].letters.length;
      entries[current].characters.data = entries[current].text;
      phase = 'hold';
    };
    function step() {
      if (!canRun()) return;
      const entry = entries[current];
      if (phase === 'typing') {
        entry.characters.data = entry.letters.slice(0, ++cursor).join('');
        if (cursor >= entry.letters.length) {
          phase = 'hold';
          notice.classList.remove('is-typing');
        }
        schedule(phase === 'hold' ? 3600 : 22);
      } else if (phase === 'hold') {
        notice.classList.add('is-leaving');
        phase = 'exit'; schedule(260);
      } else {
        current = (current + 1) % entries.length; cursor = 0; phase = 'typing';
        entries[current].characters.data = ''; show(); schedule(180);
      }
    }
    const sync = () => {
      stop();
      notice.classList.toggle('is-collapsed', !opened);
      bell.setAttribute('aria-expanded', String(opened));
      bell.setAttribute('aria-label', opened ? 'Ascunde noutățile Zion' : 'Arată noutățile Zion');
      panel.setAttribute('aria-hidden', String(!opened));
      panel.inert = !opened;
      schedule(phase === 'hold' ? 3600 : 22);
    };
    const setOpen = value => {
      opened = value;
      try { localStorage.setItem(key, value ? 'open' : 'closed'); } catch { /* Storage is optional. */ }
      sync();
    };
    bell.addEventListener('click', () => setOpen(!opened));
    notice.querySelector('.zion-updates__close').addEventListener('click', () => { bell.focus(); setOpen(false); });
    pause.addEventListener('click', () => {
      paused = !paused;
      pause.setAttribute('aria-pressed', String(paused));
      pause.setAttribute('aria-label', paused ? 'Pornește animația' : 'Oprește animația');
      pause.textContent = paused ? '▷' : 'Ⅱ';
      if (paused) complete();
      sync();
    });
    notice.querySelector('.zion-updates__next').addEventListener('click', () => {
      stop(); current = (current + 1) % entries.length; complete(); show(); schedule(3600);
    });
    const reduce = () => {
      stop();
      pause.disabled = motion.matches;
      pause.setAttribute('aria-label', motion.matches ? 'Mișcare redusă: navigare manuală' : (paused ? 'Pornește animația' : 'Oprește animația'));
      if (motion.matches || entries.length === 1) { notice.classList.remove('is-leaving'); complete(); }
      sync();
    };
    notice.addEventListener('pointerenter', event => { if (event.pointerType === 'mouse') { hovered = true; stop(); complete(); } });
    notice.addEventListener('pointerleave', () => { hovered = false; schedule(3600); });
    notice.addEventListener('focusin', () => { focused = true; stop(); complete(); });
    notice.addEventListener('focusout', event => { if (!notice.contains(event.relatedTarget)) { focused = false; schedule(3600); } });
    document.addEventListener('visibilitychange', () => { if (document.hidden) stop(); else schedule(3600); });
    window.addEventListener('pagehide', stop);
    window.addEventListener('pageshow', () => { opened = read(); sync(); });
    window.addEventListener('storage', event => { if (event.key === key || event.key === null) { opened = read(); sync(); } });
    motion.addEventListener('change', reduce);
    controls.hidden = false;
    notice.classList.add('is-enhanced');
    show(); reduce();
  });
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialise, { once: true });
  else initialise();
})();
