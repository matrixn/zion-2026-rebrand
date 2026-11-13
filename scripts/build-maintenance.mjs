import { readFile, writeFile, mkdir } from 'node:fs/promises';
const root = new URL('../', import.meta.url);
export async function maintenanceDocument() {
  const source = new URL('themes/zion-theme/maintenance/', root);
  const [html, css, js] = await Promise.all(['html', 'css', 'js'].map(type => readFile(new URL(`continuum.${type}`, source), 'utf8')));
  const theme = JSON.parse(await readFile(new URL('themes/zion-theme/theme.json', root), 'utf8'));
  const palette = new Map(theme.settings.color.palette.map(item => [item.slug, item.color.toLowerCase()]));
  for (const token of ['ink','surface','text','muted','accent','deep']) {
    if (!css.includes(`--uc-${token}: ${palette.get(token)}`)) throw new Error(`Maintenance palette drift: ${token}`);
  }
  return `<!doctype html><html lang="ro"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow,noarchive"><title>Zion 3D — Construim ce urmează</title><style>${css}</style></head><body class="zion-underconstruction">${html}<script>${js}</script></body></html>`;
}
export async function buildMaintenance() {
  await mkdir(new URL('build/', root), { recursive: true });
  await writeFile(new URL('build/zion-continuum-maintenance-1.0.0.html', root), await maintenanceDocument());
}
