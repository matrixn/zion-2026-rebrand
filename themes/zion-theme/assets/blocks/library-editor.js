(function ({ blocks, blockEditor, components, element }) {
	const h = element.createElement;
	const { InspectorControls, useBlockProps } = blockEditor;
	const { PanelBody, Placeholder, SelectControl, TextControl, ToggleControl } = components;
	const shops = [
		{ label: 'Toate produsele', value: '' },
		{ label: 'Magazin 3D', value: '3d' },
		{ label: 'Magazin Software', value: 'software' }
	];
	const textLabels = { title: 'Titlu', text: 'Text', label: 'Text buton', url: 'Link', quote: 'Citat editorial', author: 'Autor' };

	function controls(mode, attributes, setAttributes) {
		if (mode === 'services') return [];
		const text = key => h(TextControl, {
			key, label: textLabels[key], value: attributes[key] ?? '', onChange: value => setAttributes({ [key]: value })
		});
		const number = (key, label, fallback, max) => h(TextControl, {
			key, label, type: 'number', min: 1, max, value: attributes[key] ?? fallback,
			onChange: value => setAttributes({ [key]: value ? Number(value) : undefined })
		});
		if (mode === 'cta') return ['title', 'text', 'label', 'url'].map(text);
		if (mode === 'testimonials') return [text('quote'), text('author'), number('limit', 'Recenzii afișate', 3, 6)];
		const fields = [
			h(SelectControl, {
				key: 'shop', label: 'Vitrină', options: mode === 'shop' ? shops.slice(1) : shops,
				value: attributes.shop ?? (mode === 'shop' ? '3d' : ''), onChange: shop => setAttributes({ shop })
			}),
			number('limit', 'Produse pe pagină', 8, 24),
			number('columns', 'Coloane desktop', 4, 5)
		];
		if (mode === 'related') {
			fields.push(number('productId', 'ID produs de referință, opțional', '', 2147483647));
		} else {
			fields.push(h(ToggleControl, {
				key: 'paginate', label: 'Paginare numerică', checked: Boolean(attributes.paginate),
				onChange: paginate => setAttributes({ paginate })
			}));
		}
		return fields;
	}

	Object.entries(window.zionLibraryBlocks).forEach(([slug, definition]) => {
		blocks.registerBlockType(`zion/${slug}`, {
			apiVersion: 3, title: definition.title, category: 'widgets', icon: 'layout',
			usesContext: ['postId'], attributes: definition.attributes,
			edit({ attributes, setAttributes }) {
				const props = useBlockProps();
				const fields = controls(definition.mode, attributes, setAttributes);
				return h('div', props,
					fields.length ? h(InspectorControls, null, h(PanelBody, { title: 'Setări Zion' }, fields)) : null,
					h(Placeholder, { label: definition.title, instructions: 'Componenta se randează cu datele site-ului pe pagina publică.' })
				);
			},
			save: () => null
		});
	});
})(window.wp);
