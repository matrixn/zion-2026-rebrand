import assert from 'node:assert/strict';
import { performance } from 'node:perf_hooks';

const origin = new URL(process.env.ZION_TEST_URL || 'http://localhost:8917');
assert(['localhost', '127.0.0.1', '[::1]'].includes(origin.hostname), 'Benchmark uses local wp-env.');
const threeD = '/magazin-3d/';
const software = '/magazin-software/';
const combo = 'categories=zion-scale-3d&min_price=45&max_price=150&filter_stock_status=instock&filter_zion-scale-material=pla&on_sale=1&orderby=price';
const scenarios = [
  { name: '3D, first page', path: threeD, total: 502, shop: '3D' },
  { name: 'Software, first page', path: software, total: 502, shop: 'Software' },
  { name: '3D, combined filters', path: `${threeD}?${combo}`, shop: '3D' },
  { name: '3D, combined filters, page 2', path: `${threeD}?${combo}&query-73-page=2`, shop: '3D' },
  { name: '3D, shared category', path: `${threeD}?categories=zion-scale-shared`, total: 100, shop: '3D' },
  { name: 'Software, shared category', path: `${software}?categories=zion-scale-shared`, total: 100, shop: 'Software' },
  { name: '3D, empty intersection', path: `${threeD}?categories=zion-scale-software`, total: 0, shop: '3D' },
];

async function measure(path) {
  const start = performance.now();
  const response = await fetch(new URL(path, origin), { signal: AbortSignal.timeout(20_000) });
  const ttfb = performance.now() - start;
  assert.equal(response.status, 200, path);
  const html = await response.text();
  const ids = [...html.matchAll(/data-wp-key="product-item-(\d+)"/g)].map(match => Number(match[1]));
  const count = html.match(/class="zion-catalog-count[^"]*" role="status">([\d,.]+) produse?/);
  assert(count, `Missing result count for ${path}`);
  return { ttfb, ms: performance.now() - start, ids, total: Number(count[1].replace(/[,.]/g, '')), bytes: Buffer.byteLength(html), html };
}

const report = [];
for (const scenario of scenarios) {
  const first = await measure(scenario.path);
  const timings = [];
  for (let sample = 0; sample < 5; ++sample) {
    const warm = await measure(scenario.path);
    assert.deepEqual(warm.ids, first.ids, `Unstable results for ${scenario.name}`);
    assert.equal(warm.total, first.total, `Unstable count for ${scenario.name}`);
    timings.push(warm.ms);
  }

  if (scenario.total !== undefined) assert.equal(first.total, scenario.total, scenario.name);
  assert(first.ids.length <= 12, `${scenario.name} returned more than one page of cards`);
  if (scenario.total !== 0) assert(first.ids.length > 0, scenario.name);
  const other = scenario.shop === '3D' ? 'Software' : '3D';
  assert(!new RegExp(`>Zion Scale ${other} [0-9]+<`).test(first.html), scenario.name);
  const median = timings.sort((a, b) => a - b)[2];
  assert(median < 3000, `${scenario.name} exceeded the local 3-second median budget`);
  report.push({
    scenario: scenario.name,
    total: first.total,
    cards: first.ids.length,
    firstTtfbMs: Math.round(first.ttfb),
    firstResponseMs: Math.round(first.ms),
    warmMedianMs: Math.round(median),
    htmlKiB: Math.round(first.bytes / 1024),
  });
  if (scenario.name === '3D, combined filters') {
    const second = await measure(`${scenario.path}&query-73-page=2`);
    assert(second.ids.length > 0 && second.ids.every(id => !first.ids.includes(id)), 'Filtered pagination repeated products');
  }
}
console.table(report);
