document.querySelectorAll('[data-zion-copy]').forEach((button) => {
	if (!navigator.clipboard?.writeText) return;

	button.hidden = false;
	button.addEventListener('click', async () => {
		try {
			await navigator.clipboard.writeText(button.dataset.zionCopy);
			button.nextElementSibling.textContent = button.dataset.copied;
		} catch {
			button.nextElementSibling.textContent = button.dataset.zionCopy;
		}
	});
});
