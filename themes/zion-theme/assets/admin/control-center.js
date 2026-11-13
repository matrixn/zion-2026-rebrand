function initZionForm(form) {
  enhanceZionControls(form);
  for (const row of form.querySelectorAll('[data-zion-requires]:not([data-zion-requires=""])')) {
    const controller = form.elements.namedItem(`values[${row.dataset.zionRequires}]`);
    if (!controller) continue;
    const update = () => {
      row.dataset.zionRequiresHidden = String((controller.value || controller.dataset.zionInherited) !== '1');
      row.hidden = row.dataset.zionRequiresHidden === 'true' || row.dataset.zionSearchHidden === 'true';
      for (const field of row.querySelectorAll('input, select')) field.disabled = row.hidden;
    };
    controller.addEventListener('change', update);
    update();
  }

  for (const picker of form.querySelectorAll('[data-zion-design-picker]')) {
    const select = form.querySelector(`#${picker.dataset.control}`);
    const choices = [...picker.querySelectorAll('[data-zion-design]')];
    const figure = picker.querySelector('figure');
    const image = figure.querySelector('img');
    const opener = figure.querySelector('a');
    const dialog = picker.querySelector('dialog');
    if (dialog && typeof dialog.showModal === 'function') {
      opener.setAttribute('aria-haspopup', 'dialog');
      opener.addEventListener('click', event => {
        event.preventDefault();
        const large = dialog.querySelector('img');
        large.src = image.src;
        large.alt = image.alt;
        dialog.querySelector('.zion-preview-caption').textContent = figure.querySelector('figcaption').textContent;
        dialog.showModal();
        dialog.querySelector('button').focus();
      });
      dialog.querySelector('button').addEventListener('click', () => dialog.close());
      dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
      dialog.addEventListener('close', () => opener.focus({ preventScroll: true }));
    }
    const update = () => {
      const value = select.value || select.dataset.zionInherited;
      const selected = choices.find(choice => choice.dataset.zionDesign === value);
      for (const choice of choices) choice.setAttribute('aria-pressed', String(choice === selected));
      figure.hidden = !selected;
      if (!selected) return;
      image.alt = `Preview ${selected.textContent.trim()}`;
      figure.querySelector('a').href = selected.dataset.preview;
      image.src = selected.dataset.preview;
      figure.querySelector('figcaption').textContent = selected.dataset.description;
    };
    select.addEventListener('change', update);
    for (const choice of choices) {
      choice.addEventListener('click', () => {
        select.value = choice.dataset.zionDesign;
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });
    }
    update();
  }

  form.addEventListener('submit', async event => {
    const operation = event.submitter?.value;
    if (!window.ajaxurl || !['save', 'reset_section'].includes(operation)) return;
    event.preventDefault();
    if (form.getAttribute('aria-busy') === 'true') return;
    const feedback = form.querySelector('.zion-control-feedback');
    const data = new FormData(form);
    data.set('operation', operation);
    form.setAttribute('aria-busy', 'true');
    feedback.textContent = 'Se salvează…';
    const buttons = [...form.querySelectorAll('button')];
    for (const button of buttons) button.disabled = true;
    try {
      const response = await fetch(window.ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' });
      const text = await response.text();
      if (!response.ok) {
        const message = new DOMParser().parseFromString(text, 'text/html').body.textContent.trim();
        throw new Error(message || 'Salvarea a eșuat. Încearcă din nou.');
      }
      const result = JSON.parse(text);
      if (!result.success || !result.data?.html) throw new Error('Răspuns de salvare invalid. Reîncarcă panoul.');
      const next = new DOMParser().parseFromString(result.data.html, 'text/html').querySelector('[data-zion-section]');
      if (!next) throw new Error('Formularul actualizat lipsește. Reîncarcă panoul.');
      form.replaceWith(next);
      initZionForm(next);
      next.querySelector('.zion-control-feedback').textContent = result.data.message;
      const notices = document.querySelector('[data-zion-control-notices]');
      if (notices) notices.replaceChildren(...new DOMParser().parseFromString(result.data.notices || '', 'text/html').body.childNodes);
      next.querySelector(`button[value="${operation}"]`).focus({ preventScroll: true });
      const revision = next.elements.namedItem('revision').value;
      for (const input of document.querySelectorAll('input[name="revision"]')) input.value = revision;
    } catch (error) {
      feedback.textContent = error.message;
      feedback.setAttribute('role', 'alert');
    } finally {
      form.removeAttribute('aria-busy');
      for (const button of buttons) button.disabled = false;
    }
  });
}

document.querySelectorAll('[data-zion-section]').forEach(initZionForm);

function enhanceZionControls(form) {
  for (const row of form.querySelectorAll('[data-zion-field]')) {
    const field = form.elements.namedItem(`values[${row.dataset.zionField}]`);
    if (!field) continue;
    const holder = row.querySelector('td');
    if (row.dataset.zionType === 'bool') {
      const toggle = document.createElement('button');
      toggle.type = 'button'; toggle.className = 'zion-toggle'; toggle.setAttribute('role', 'switch');
      toggle.setAttribute('aria-label', `Comută ${row.dataset.zionTitle}`);
      const update = () => { const enabled = (field.value || field.dataset.zionInherited) === '1'; toggle.setAttribute('aria-checked', String(enabled)); toggle.textContent = enabled ? 'Activat' : 'Dezactivat'; };
      toggle.addEventListener('click', () => { field.value = (field.value || field.dataset.zionInherited) === '1' ? '0' : '1'; field.dispatchEvent(new Event('change', { bubbles: true })); });
      field.addEventListener('change', update); holder.insertBefore(toggle, field); update();
    }
    if (row.dataset.zionType === 'int') {
      const range = document.createElement('input'); range.type = 'range'; range.min = field.min; range.max = field.max; range.step = '1'; range.className = 'zion-range'; range.setAttribute('aria-label', `${row.dataset.zionTitle} · glisor`);
      const update = () => { range.value = field.value || row.dataset.zionValue; };
      range.addEventListener('input', () => { field.value = range.value; field.dispatchEvent(new Event('input', { bubbles: true })); });
      field.addEventListener('input', update); holder.insertBefore(range, field); update();
    }
    if (row.dataset.zionType === 'ordered_keys') {
      const labels = JSON.parse(row.dataset.zionChoices),list = document.createElement('ol'); list.className = 'zion-order-list'; list.setAttribute('aria-label', `Ordine ${row.dataset.zionTitle}`);
      let dragged;
      const write = keys => { field.value = keys.join(','); field.dispatchEvent(new Event('input', { bubbles: true })); render(); };
      const keys = () => (field.value || row.dataset.zionValue).split(',').map(k => k.trim()).filter(k => Object.hasOwn(labels, k));
      const move = (key,delta) => { const order = keys(),from = order.indexOf(key),to = from + delta; if(from < 0 || to < 0 || to >= order.length) return; [order[from],order[to]] = [order[to],order[from]]; write(order); list.querySelector(`[data-zion-key="${key}"] button:not(:disabled),[data-zion-key="${key}"] input`).focus(); };
      const render = () => {
        list.replaceChildren(); const order = keys();
        for (const key of [...order,...Object.keys(labels).filter(k => !order.includes(k))]) {
          const li = document.createElement('li'); li.dataset.zionKey = key;
          const label = document.createElement('label'),check = document.createElement('input'); check.type = 'checkbox'; check.checked = order.includes(key); check.setAttribute('aria-label', `Include ${labels[key]}`); label.append(check,document.createTextNode(labels[key])); li.append(label);
          check.addEventListener('change', () => { const next = check.checked ? [...order,key] : order.filter(k => k !== key); if(next.length) write(next); else check.checked = true; });
          for(const [delta,text] of [[-1,'↑'],[1,'↓']]) {
            const button = document.createElement('button'); button.type = 'button'; button.textContent = text; button.className = 'button'; button.setAttribute('aria-label', `Mută ${labels[key]} mai ${delta < 0 ? 'sus' : 'jos'}`); button.disabled = !order.includes(key) || (delta < 0 ? order.indexOf(key) === 0 : order.indexOf(key) === order.length-1); button.addEventListener('click', () => move(key,delta)); li.append(button);
          }
          li.draggable = order.includes(key);
          li.addEventListener('dragstart', e => { dragged = key; e.dataTransfer.setData('text/plain',key); });
          li.addEventListener('dragover', e => { if(dragged && order.includes(key)) e.preventDefault(); });
          li.addEventListener('drop', e => { e.preventDefault(); if(!dragged || dragged === key || !order.includes(key)) return; const next = order.filter(k => k !== dragged); next.splice(next.indexOf(key),0,dragged); dragged = undefined; write(next); });
          li.addEventListener('dragend', () => { dragged = undefined; }); list.append(li);
        }
      };
      holder.insertBefore(list,field); field.addEventListener('change',render); render();
    }
  }
  const changed = () => { const feedback = form.querySelector('.zion-control-feedback'); if(form.getAttribute('aria-busy') !== 'true') feedback.textContent = 'Modificări nesalvate.'; };
  form.addEventListener('input',changed); form.addEventListener('change',changed);
}

document.querySelector('[data-zion-setting-search]')?.addEventListener('input', event => {
  const normalize = text => text.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
  const term = normalize(event.target.value);
  for(const row of document.querySelectorAll('[data-zion-field]')) {
    row.dataset.zionSearchHidden = String(!normalize(row.dataset.zionTitle + ' ' + row.querySelector('.description').textContent).includes(term));
    row.hidden = row.dataset.zionSearchHidden === 'true' || row.dataset.zionRequiresHidden === 'true';
  }
});
