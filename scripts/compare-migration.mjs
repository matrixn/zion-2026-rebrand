import { readFile } from 'node:fs/promises';

const [beforePath, afterPath] = process.argv.slice(2);
if (!beforePath || !afterPath) throw new Error('Usage: node scripts/compare-migration.mjs before.json after.json');
const [before, after] = await Promise.all([beforePath, afterPath].map(async path => JSON.parse(await readFile(path, 'utf8'))));
if (!Array.isArray(before.content) || !Array.isArray(after.content)) throw new Error('Both inputs must be Zion audit reports.');

function route(url) {
  if (!url) return '';
  const parsed = new URL(url);
  return parsed.pathname + parsed.search;
}

const changes = [];
const afterById = new Map(after.content.map(row => [row.id, row]));
for (const source of before.content) {
  const target = afterById.get(source.id);
  if (!target) {
    changes.push({ id: source.id, change: 'missing', url: source.url });
    continue;
  }
  afterById.delete(source.id);
  const fields = {};
  for (const key of ['content_sha256', 'status', 'type', 'seo', 'taxonomies', 'product', 'dependencies']) {
    if (JSON.stringify(source[key]) !== JSON.stringify(target[key])) fields[key] = { before: source[key], after: target[key] };
  }
  if (route(source.url) !== route(target.url)) fields.route = { before: route(source.url), after: route(target.url) };
  if (Object.keys(fields).length) changes.push({ id: source.id, title: source.title, fields });
}
for (const row of afterById.values()) changes.push({ id: row.id, change: 'added', url: row.url });

console.log(JSON.stringify({
  before: before.site,
  after: after.site,
  routeChanges: changes.filter(row => row.change === 'missing' || row.fields?.route).length,
  remainingElementorBuilder: after.content.filter(row => row.dependencies?.elementor_builder).map(row => row.id),
  retainedElementorData: after.content.filter(row => row.dependencies?.elementor_data).map(row => row.id),
  remainingXStoreCandidates: after.content.filter(row => row.dependencies?.xstore_candidates?.length).map(row => row.id),
  sidebarWidgetsChanged: JSON.stringify(before.sidebar_widgets) !== JSON.stringify(after.sidebar_widgets),
  xstoreSidebarCandidates: after.xstore_sidebar_candidates,
  commerceChanged: JSON.stringify(before.commerce) !== JSON.stringify(after.commerce),
  pluginsChanged: JSON.stringify(before.plugins) !== JSON.stringify(after.plugins),
  reading: { before: before.reading, after: after.reading },
  changes,
}, null, 2));
