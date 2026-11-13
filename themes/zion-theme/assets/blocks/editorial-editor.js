(function (blocks, editor, element) {
	const labels = {
		back_top: 'Înapoi sus',
		breadcrumb: 'Fir de navigare',
		reading_time: 'Timp estimat de citire',
		related: 'Articole asociate',
		share: 'Distribuire articol',
		journal_status: 'Număr de articole în interogarea WordPress',
		journal_categories: 'Categorii native din jurnal',
		journal_search_term: 'Termenul căutat în jurnal',
		card_link: 'Legătură către articol',
		article_breadcrumb: 'Fir de navigare al articolului',
		article_return: 'Înapoi la jurnal',
		reading_progress: 'Progresul lecturii',
		software_intro: 'Context și introducere Software',
		software_actions: 'Legături Software',
		software_breadcrumb: 'Fir de navigare Software',
		software_toc: 'Secțiunile paginii Software',
		software_handoff: 'CTA Software'
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
