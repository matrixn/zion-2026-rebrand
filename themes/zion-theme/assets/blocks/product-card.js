document.addEventListener('click', (event) => {
	const trigger = event.target.closest('a[data-zion-quick-view]');
	if (!trigger || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

	const dialog = trigger.nextElementSibling;
	if (typeof dialog?.showModal !== 'function' || dialog.open) return;

	event.preventDefault();
	dialog.addEventListener('close', () => trigger.focus(), { once: true });
	dialog.showModal();
});
