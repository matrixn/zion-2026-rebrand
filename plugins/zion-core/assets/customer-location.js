/* Preserve WooCommerce's native country metadata and address components. */
(() => {
  const data = window.wc?.wcSettings?.getSetting('countryData', null);
  if (!data || !Object.hasOwn(data, 'RO')) return;
  const entries = Object.entries(data), romania = data.RO;
  for (const key of Object.keys(data)) delete data[key];
  data.RO = romania;
  for (const [key, value] of entries) if (key !== 'RO') data[key] = value;
})();
