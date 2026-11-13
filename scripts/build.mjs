import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { transform } from 'lightningcss';
import { buildMaintenance } from './build-maintenance.mjs';

const destination = new URL('../themes/zion-theme/assets/dist/', import.meta.url);
await buildMaintenance();
await mkdir(destination, { recursive: true });
await Promise.all(['zion.css', 'collections.css', 'library-content.css'].map(async filename => {
  const source = new URL(`../themes/zion-theme/assets/src/${filename}`, import.meta.url);
  const { code } = transform({ filename, code: await readFile(source), minify: true });
  await writeFile(new URL(filename, destination), code);
}));
