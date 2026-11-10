import { readFile } from 'node:fs/promises';

const [input, expected] = process.argv.slice(2);
if (!input || !expected) throw new Error('Usage: node scripts/crawl-migration.mjs audit.json clone-url');
const audit = JSON.parse(await readFile(input, 'utf8'));
const base = new URL(expected);
const host = base.hostname.toLowerCase().replace(/\.$/, '');
const local = ['localhost', '127.0.0.1', '[::1]'].includes(host) || /\.(?:test|localhost)$/.test(host);
if (audit.site !== expected || base.username || base.password ||
    ['zion3d.ro', 'www.zion3d.ro'].includes(host) ||
    !(base.protocol === 'https:' || (local && base.protocol === 'http:'))) {
  throw new Error('Only an explicitly matching local or HTTPS staging audit is accepted.');
}

function attributes(tag) {
  return Object.fromEntries([...tag.matchAll(/([\w:-]+)\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s>]+))/g)]
    .map(match => [match[1].toLowerCase(), match[2] ?? match[3] ?? match[4]]));
}

async function inspect(row) {
  const url = new URL(row.url);
  if (url.origin !== base.origin || url.username || url.password) return { id: row.id, url: row.url, skipped: 'outside clone origin' };
  try {
    const response = await fetch(url, { redirect: 'manual', signal: AbortSignal.timeout(15_000) });
    const result = { id: row.id, url: row.url, status: response.status, location: response.headers.get('location') };
    if (!response.ok) return result;
    const head = (await response.text()).split(/<\/head\s*>/i)[0];
    const tags = [...head.matchAll(/<(?:meta|link)\s+[^>]+>/gi)].map(match => attributes(match[0]));
    return {
      ...result,
      title: head.match(/<title[^>]*>([^<]*)<\/title>/i)?.[1] ?? '',
      canonicals: tags.filter(tag => tag.rel === 'canonical').map(tag => tag.href),
      descriptions: tags.filter(tag => tag.name === 'description').map(tag => tag.content),
      robots: tags.filter(tag => tag.name === 'robots').map(tag => tag.content),
      robotsHeader: response.headers.get('x-robots-tag'),
    };
  } catch (error) {
    return { id: row.id, url: row.url, error: error.message };
  }
}

const rows = audit.content.filter(row => row.url);
const results = [];
for (let offset = 0; offset < rows.length; offset += 4) {
  results.push(...await Promise.all(rows.slice(offset, offset + 4).map(inspect)));
}
console.log(JSON.stringify({ site: expected, urls: results }, null, 2));
