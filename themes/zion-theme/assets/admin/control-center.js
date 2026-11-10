document.querySelectorAll('[data-zion-section]').forEach(form => {
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
});
