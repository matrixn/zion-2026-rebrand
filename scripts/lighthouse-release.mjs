import { execFile } from 'node:child_process';
import { mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { promisify } from 'node:util';
import { chromium } from '@playwright/test';

const execute = promisify(execFile);
const root = new URL(process.env.ZION_TEST_URL || 'http://localhost:8917/');
if (!['localhost', '127.0.0.1', '[::1]'].includes(root.hostname) || root.username || root.password) {
  throw new Error('The release Lighthouse script runs only on a local wp-env site.');
}
if (!process.env.npm_execpath) throw new Error('Run through npm run test:lighthouse.');
const directory = resolve('build/lighthouse');
await mkdir(directory, { recursive: true });
const summary = [];
for (const [route, slug] of [['/', 'home'], ['/magazin-3d/', '3d'], ['/product/zion-catalog-tools/', 'product']]) {
  for (const device of ['mobile', 'desktop']) {
    const file = resolve(directory, `${slug}-${device}.json`);
    const profile = await mkdtemp(join(tmpdir(), 'zion-lighthouse-'));
    let browser;
    try {
      browser = await chromium.launchPersistentContext(profile, { channel: 'chromium', args: ['--remote-debugging-port=0', '--disable-features=PaintHolding'] });
      const port = (await readFile(join(profile, 'DevToolsActivePort'), 'utf8')).split('\n')[0];
      const args = [
        process.env.npm_execpath, 'exec', '--yes', '--package=lighthouse@13.6.0', '--', 'lighthouse',
        new URL(route, root).href, '--verbose', '--output=json', `--output-path=${file}`, `--port=${port}`,
        '--only-categories=performance,accessibility,seo', '--max-wait-for-load=25000', '--max-wait-for-fcp=20000',
      ];
      if (device === 'desktop') args.push('--preset=desktop');
      await execute(process.execPath, args, { timeout: 120_000 }).catch(error => { console.error(error.stderr); throw new Error(`Lighthouse failed for ${route} ${device}.`, { cause: error }); });
    } finally {
      await browser?.close();
      await rm(profile, { recursive: true, force: true });
    }
    const report = JSON.parse(await readFile(file, 'utf8'));
    if (report.runtimeError) throw new Error(report.runtimeError.message);
    summary.push({
      route, device,
      performance: Math.round(report.categories.performance.score * 100),
      accessibility: Math.round(report.categories.accessibility.score * 100),
      seo: Math.round(report.categories.seo.score * 100),
      lcpMs: Math.round(report.audits['largest-contentful-paint'].numericValue),
      cls: report.audits['cumulative-layout-shift'].numericValue,
      tbtMs: Math.round(report.audits['total-blocking-time'].numericValue),
    });
  }
}
await writeFile(resolve(directory, 'summary.json'), JSON.stringify(summary, null, 2) + '\n');
console.table(summary);
