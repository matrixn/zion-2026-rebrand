(function (blocks, editor, element) {
	blocks.registerBlockType('zion/product-page', {
		apiVersion: 3,
		title: 'Pagină produs Zion',
		category: 'widgets',
		icon: 'products',
		supports: { multiple: false },
		usesContext: ['postId', 'postType'],
		edit: function () {
			return element.createElement('div', editor.useBlockProps({ className: 'zion-card' }),
				element.createElement('strong', null, 'Pagină produs Zion'),
				element.createElement('span', null, 'Configurează presetul și secțiunile în Aspect → Zion Theme.'));
		},
		save: function () { return null; }
	});
})(window.wp.blocks, window.wp.blockEditor, window.wp.element);
