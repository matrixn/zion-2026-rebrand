#!/usr/bin/env bash
set -euo pipefail

# The runner keeps Node/npm and wp-env; only the browser runs in the official image.
container_name="${ZION_BROWSER_CONTAINER:-zion-playwright-local}"
case "${1:-}" in
  start)
    version="$(node -p "require('./node_modules/playwright/package.json').version")"
    image="mcr.microsoft.com/playwright:v${version}-jammy"
    docker pull "$image"
    docker run --detach --rm --init --network host --ipc host \
      --name "$container_name" --volume "$PWD:/work:ro" --workdir /work \
      "$image" node node_modules/playwright/cli.js run-server --host 127.0.0.1 --port 9347
    if ! node --input-type=module <<'JS'
for (let attempt = 0; attempt < 60; attempt++) {
  try {
    await fetch('http://127.0.0.1:9347', { signal: AbortSignal.timeout(1000) });
    process.exit(0);
  } catch { await new Promise(resolve => setTimeout(resolve, 1000)); }
}
process.exit(1);
JS
    then
      docker logs "$container_name"
      docker stop "$container_name"
      exit 1
    fi
    ;;
  stop)
    if docker container inspect "$container_name" >/dev/null 2>&1; then
      docker logs "$container_name"
      docker stop "$container_name"
    fi
    ;;
  *) echo 'Usage: bash scripts/ci-browser.sh start|stop' >&2; exit 2 ;;
esac
