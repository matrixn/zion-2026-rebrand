/* Zion Pulse: isolated, local state. No network, hardware or persistent storage. */
(() => {
  'use strict';
  const root = document.querySelector('.zion-pulse-page .zion-pulse');
  const header = document.querySelector('.wp-site-blocks > header');
  if (root && header) {
    const measure = () => root.style.setProperty('--pulse-header-offset', `${Math.ceil(header.getBoundingClientRect().height + 32)}px`);
    measure();
    if ('ResizeObserver' in window) new ResizeObserver(measure).observe(header);
  }
  document.querySelectorAll('[data-pulse-demo]').forEach(demo => {
    const keys = [...demo.querySelectorAll('[data-pulse-key]')];
    const order = ['verde', 'galben', 'albastru', 'rosu', 'alb'];
    const labels = ['Verde', 'Galben', 'Albastru', 'Roșu', 'Alb'];
    let progress = 0;
    let solved = false;
    const update = (message, state = 'active') => {
      demo.dataset.state = state;
      demo.querySelector('[data-pulse-state]').textContent = solved ? 'Secvență rezolvată' : (progress ? 'Secvență în curs' : 'În așteptare');
      demo.querySelector('[data-pulse-progress]').textContent = `${progress} / 5`;
      demo.querySelector('[data-pulse-door]').textContent = solved ? 'Deblocată' : 'Blocată';
      demo.querySelector('[data-pulse-meter]').style.width = `${progress * 20}%`;
      demo.querySelector('[data-pulse-message]').textContent = message;
      keys.forEach((key, index) => {
        const active = index < progress;
        key.setAttribute('aria-pressed', String(active));
        key.querySelector('[data-pulse-key-status]').textContent = active ? 'Confirmat ✓' : 'În așteptare';
      });
    };
    keys.forEach(key => key.addEventListener('click', () => {
      if (solved) return;
      if (key.dataset.pulseKey !== order[progress]) {
        progress = 0;
        update('Secvență greșită. Am resetat progresul. Începe cu Verde.', 'error');
        return;
      }
      progress += 1;
      solved = progress === order.length;
      update(solved ? 'Secvență completă. Cutia virtuală este deblocată! Poți relua cu RESET.' : `${labels[progress - 1]} confirmat. Urmează ${labels[progress]}.`, solved ? 'solved' : 'active');
    }));
    demo.querySelector('[data-pulse-reset]').addEventListener('click', () => {
      solved = false;
      progress = 0;
      update('Simulare resetată. Începe cu Verde.', 'ready');
    });
    demo.querySelector('[data-pulse-solve]').addEventListener('click', () => {
      progress = 5;
      solved = true;
      update('FORCE SOLVE demonstrativ. Cutia virtuală este deblocată. Poți relua cu RESET.', 'solved');
    });
    demo.querySelectorAll('button').forEach(button => { button.disabled = false; });
    demo.querySelector('[data-pulse-fallback]').hidden = true;
    demo.dataset.enhanced = 'true';
  });
  document.querySelectorAll('[data-pulse-modules]').forEach(modules => {
    const buttons = [...modules.querySelectorAll('[data-pulse-module]')];
    buttons.forEach(button => { button.disabled = false; });
    buttons.forEach(button => button.addEventListener('click', () => {
      buttons.forEach(item => item.setAttribute('aria-pressed', String(item === button)));
      modules.querySelector('[data-pulse-module-description]').textContent = button.dataset.description;
    }));
  });
  const preference = window.matchMedia('(prefers-reduced-motion: reduce)');
  document.querySelectorAll('[data-pulse-motion-scene]').forEach(scene => {
    const button = scene.querySelector('[data-pulse-motion]');
    let paused = preference.matches;
    const update = () => {
      scene.classList.toggle('is-paused', paused || preference.matches);
      button.setAttribute('aria-pressed', String(paused || preference.matches));
      button.textContent = preference.matches ? 'Animație redusă' : (paused ? 'Pornește animația' : 'Pauză animație');
      button.disabled = preference.matches;
    };
    button.addEventListener('click', () => { paused = !paused; update(); });
    preference.addEventListener('change', update);
    update();
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(entries => scene.classList.toggle('is-visible', entries[0].isIntersecting), { threshold: 0.05 });
      observer.observe(scene);
    } else scene.classList.add('is-visible');
    document.addEventListener('visibilitychange', () => scene.classList.toggle('is-document-hidden', document.hidden));
  });
})();
