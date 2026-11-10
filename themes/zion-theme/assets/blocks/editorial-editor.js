(function (blocks, editor, element) {
	const labels = {
		back_top: 'Înapoi sus',
		breadcrumb: 'Fir de navigare',
		reading_time: 'Timp estimat de citire',
		related: 'Articole asociate',
		share: 'Distribuire articol'
	};
	blocks.registerBlockType('zion/editorial', {
		apiVersion: 3,
		title: 'Componentă editorială Zion',
		category: 'widgets',
		usesContext: ['postId'],
		attributes: { part: { type: 'string' } },
		supports: { inserter: false },
		edit: function ({ attributes }) {
			return element.createElement('div', editor.useBlockProps(), labels[attributes.part] || 'Componentă editorială Zion');
		},
		save: function () { return null; }
	});
})(window.wp.blocks, window.wp.blockEditor, window.wp.element);
