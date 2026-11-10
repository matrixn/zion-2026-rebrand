import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { join } from 'node:path';

const [input, directory = 'build/migration'] = process.argv.slice(2);
if (!input) throw new Error('Provide the test:migration log file.');
const lines = (await readFile(input, 'utf8')).split(/\r?\n/);
const names = { ZION_AUDIT_BEFORE: 'audit-before', ZION_AUDIT_AFTER: 'audit-after', ZION_DRY_RUN: 'dry-run', ZION_REHEARSAL: 'rehearsal' };
await mkdir(directory, { recursive: true });
for (const [key, name] of Object.entries(names)) {
  const line = lines.find(entry => entry.startsWith(key + ' '));
  if (!line) throw new Error(`Missing ${key}. The rehearsal did not complete.`);
  const report = JSON.parse(line.slice(key.length + 1));
  await writeFile(join(directory, name + '.json'), JSON.stringify(report, null, 2) + '\n', { mode: 0o600 });
}
