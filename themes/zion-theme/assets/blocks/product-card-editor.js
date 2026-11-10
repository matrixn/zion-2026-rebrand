(function (blocks, editor, element) {
	blocks.registerBlockType('zion/product-card', {
		apiVersion: 3,
		title: 'Card produs Zion',
		category: 'widgets',
		icon: 'cart',
		attributes: { shop: { type: 'string' }, level: { type: 'integer', default: 2 } },
		usesContext: ['postId', 'postType'],
		edit: function () {
			return element.createElement('div', editor.useBlockProps({ className: 'zion-card' }),
				element.createElement('strong', null, 'Card produs Zion'),
				element.createElement('span', null, 'Aspectul și informațiile sunt configurate în Aspect → Zion Theme.'));
		},
		save: function () { return null; }
	});
})(window.wp.blocks, window.wp.blockEditor, window.wp.element);
