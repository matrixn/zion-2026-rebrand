import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { transform } from 'lightningcss';

const source = new URL('../themes/zion-theme/assets/src/zion.css', import.meta.url);
const destination = new URL('../themes/zion-theme/assets/dist/', import.meta.url);
const { code } = transform({
  filename: 'zion.css',
  code: await readFile(source),
  minify: true,
});
await mkdir(destination, { recursive: true });
await writeFile(new URL('zion.css', destination), code);
