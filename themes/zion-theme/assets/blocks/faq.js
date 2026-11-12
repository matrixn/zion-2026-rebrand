/* Progressive FAQ controls. Native Details owns disclosure, keyboard and no-JS behavior. */
(() => {
  'use strict';
  for (const root of document.querySelectorAll('.zion-faq')) {
    const content = root.querySelector('.zion-faq-content');
    const items = [...(content?.querySelectorAll('details.wp-block-details') || [])];
    if (!items.length) continue;
    items.forEach((item, index) => { const summary = item.querySelector('summary'); if (summary) summary.dataset.zionFaqIndex = String(index + 1).padStart(2, '0'); });
    const node = (tag, className, text) => { const element = document.createElement(tag); element.className = className; if (text) element.textContent = text; return element; };
    const controls = node('div', 'zion-faq-controls');
    const label = node('label', 'zion-faq-search-label', 'Caută în întrebări și răspunsuri');
    const input = node('input', 'zion-faq-search'); input.type = 'search'; input.placeholder = 'Fișiere, materiale, dimensiuni…';
    label.append(input);
    const actions = node('div', 'zion-faq-actions');
    const button = text => { const e = node('button', 'zion-faq-control', text); e.type = 'button'; actions.append(e); return e; };
    const clear = button('Șterge căutarea'), expand = button('Deschide toate'), collapse = button('Închide toate');
    const status = node('p', 'zion-faq-status'); status.setAttribute('role', 'status'); status.setAttribute('aria-live', 'polite'); status.setAttribute('aria-atomic', 'true');
    const empty = node('p', 'zion-faq-empty', 'Nu am găsit un răspuns pentru această căutare. Încearcă alt cuvânt sau scrie-ne folosind legătura Contact.'); empty.hidden = true;
    controls.append(label, actions, status); content.before(controls); content.after(empty);
    const normalize = value => value.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLocaleLowerCase('ro').trim();
    const texts = items.map(item => normalize(item.textContent));
    const update = () => {
      const visible = items.filter(item => !item.hidden), opened = visible.filter(item => item.open).length;
      status.textContent = `${visible.length} ${visible.length === 1 ? 'întrebare' : 'întrebări'} · ${opened} ${opened === 1 ? 'răspuns deschis' : 'răspunsuri deschise'}`;
      clear.disabled = !input.value; expand.disabled = !visible.length || opened === visible.length; collapse.disabled = !opened;
      empty.hidden = !!visible.length;
      root.dispatchEvent(new CustomEvent('zion:faq-change'));
    };
    const search = () => { const words = normalize(input.value).split(/\s+/).filter(Boolean); items.forEach((item, i) => { item.hidden = !words.every(word => texts[i].includes(word)); }); update(); };
    input.addEventListener('input', search);
    input.addEventListener('keydown', event => { if (event.key === 'Escape' && input.value) { event.preventDefault(); input.value = ''; search(); } });
    clear.addEventListener('click', () => { input.value = ''; search(); input.focus(); });
    expand.addEventListener('click', () => { items.filter(item => !item.hidden).forEach(item => { item.open = true; }); update(); });
    collapse.addEventListener('click', () => { items.filter(item => !item.hidden).forEach(item => { item.open = false; }); update(); });
    items.forEach(item => item.addEventListener('toggle', update));
    root.classList.add('is-faq-enhanced'); update();
  }
})();
