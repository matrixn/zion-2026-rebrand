function initZionForm(form) {
  for (const row of form.querySelectorAll('[data-zion-requires]:not([data-zion-requires=""])')) {
    const controller = form.elements.namedItem(`values[${row.dataset.zionRequires}]`);
    if (!controller) continue;
    const update = () => {
      row.hidden = (controller.value || controller.dataset.zionInherited) !== '1';
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
