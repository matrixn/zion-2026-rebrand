/* Zion Continuum: one scheduler and projection system, contextual sculptures. */
(() => {
  'use strict';
  const root = document.querySelector('.zion-about, .zion-homepage');
  if (!root || !window.IntersectionObserver || !window.ResizeObserver) return;
  const motion = matchMedia('(prefers-reduced-motion: reduce)');
  const finePointer = matchMedia('(pointer: fine)');
  const compact = matchMedia('(max-width: 600px)').matches;
  const rootStyle = getComputedStyle(document.documentElement);
  const color = name => rootStyle.getPropertyValue(`--wp--preset--color--${name}`).trim();
  const palette = { accent: color('accent'), text: color('text'), muted: color('muted'), ink: color('ink') };
  const home = root.classList.contains('zion-homepage');
  const clamp = (x, a = 0, b = 1) => Math.max(a, Math.min(b, x));
  const mix = (a, b, t) => a + (b - a) * t;
  const smooth = x => { x = clamp(x); return x * x * (3 - 2 * x); };
  const polygon = [[-1, -1], [1, -1], [1, -.66], [-.42, .66], [1, .66], [1, 1], [-1, 1], [-1, .66], [.42, -.66], [-1, -.66]];
  let randomState = 7919;
  const random = () => { randomState = (randomState * 16807) % 2147483647; return randomState / 2147483647; };
  const points = [];
  for (let i = 0; i < (compact ? 360 : 780); i++) {
    let x, y;
    do { x = random() * 2 - 1; y = random() * 2 - 1; } while (Math.abs(y) < .67 && Math.abs(x + y) > .25);
    const layer = i % 9;
    points.push({ x, y, z: (layer - 4) * .105, layer, weight: random(), code: [Math.round((random() * 2 - 1) * 6) / 4, Math.round((random() * 2 - 1) * 4) / 3, (random() - .5) * 1.7] });
  }
  const scenes = [];
  let paused = false, raf = 0, last = 0, clock = 0, scrollProgress = 0, topInset = 110;
  const reduced = () => motion.matches || document.body.classList.contains('zion-motion-reduced');
  let phaseButtons = [];
  const pauseButtons = [];

  for (const canvas of root.querySelectorAll('[data-zion-spatial]')) {
    const context = canvas.getContext('2d', { alpha: true });
    if (!context) continue;
    const host = canvas.parentElement;
    const forge = canvas.dataset.zionSpatial === 'forge';
    const type = canvas.dataset.zionSpatial;
    const section = home ? host.closest('.zion-overview-band') : (forge ? host.closest('.zion-about-forge') : host);
    const scene = { canvas, context, host, section, forge, type, active: 0, scroll: .25, visible: false, width: 0, height: 0, pointer: [0, 0], aim: [0, 0], progress: forge ? 0 : 2, selected: null, rect: null };
    scenes.push(scene);
    host.classList.add('is-spatial-ready');
    if (type === 'home-hero') host.closest('.zion-home-lab')?.classList.add('is-continuum-lab');
    canvas.dataset.renderMode = reduced() ? 'static' : 'animated';
    if (!forge) {
      host.addEventListener('pointermove', event => {
        if (!finePointer.matches || reduced() || paused) return;
        const rect = scene.rect;
        if (rect) scene.aim = [clamp((event.clientX - rect.left) / rect.width * 2 - 1, -1, 1), clamp((event.clientY - rect.top) / rect.height * 2 - 1, -1, 1)];
      }, { passive: true });
      host.addEventListener('pointerleave', () => { scene.aim = [0, 0]; }, { passive: true });
    }
  }
  if (!scenes.length) return;
  const forge = scenes.find(scene => scene.forge);
  if (home) {
    const network = scenes.find(scene => scene.type === 'home-services');
    root.querySelectorAll('.zion-home-service').forEach((card, index) => {
      const select = () => { if (network) { network.active = index + 1; draw(network); } };
      const clear = () => { if (network) { network.active = 0; draw(network); } };
      card.addEventListener('pointerenter', select);
      card.addEventListener('pointerleave', clear);
      card.addEventListener('focusin', select);
      card.addEventListener('focusout', clear);
    });
  }
  if (forge) {
    const controls = document.createElement('div');
    controls.className = 'zion-forge-controls';
    controls.setAttribute('role', 'group');
    controls.setAttribute('aria-label', 'Etapele transformării Zion');
    phaseButtons = [...forge.section.querySelectorAll('.zion-forge-phase')].map((phase, index) => {
      if (!phase.id) phase.id = `zion-forge-phase-${index + 1}`;
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = `0${index + 1} / ${phase.querySelector('h3')?.textContent ?? ''}`;
      button.setAttribute('aria-controls', phase.id);
      button.setAttribute('aria-pressed', index === 0 ? 'true' : 'false');
      button.addEventListener('click', () => {
        forge.selected = index;
        forge.progress = index;
        updatePhase(index);
        draw(forge);
      });
      controls.append(button);
      return button;
    });
    forge.host.after(controls);
  }
  // This control also pauses the inherited CAD background, including the hero.
  for (const scene of scenes) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'zion-spatial-pause';
    button.textContent = 'Pauză animație';
    button.setAttribute('aria-pressed', 'false');
    button.addEventListener('click', () => {
      paused = !paused;
      pauseButtons.forEach(control => {
        control.setAttribute('aria-pressed', String(paused));
        control.textContent = paused ? 'Reia animația' : 'Pauză animație';
      });
      root.classList.toggle('is-spatial-paused', paused);
      if (!paused) { if (forge) forge.selected = null; schedule(); }
      else { cancelAnimationFrame(raf); raf = 0; last = 0; }
    });
    (scene.forge ? scene.section.querySelector('.zion-forge-stage') : scene.host).append(button);
    pauseButtons.push(button);
  }

  function updatePhase(index) {
    if (!forge) return;
    forge.section.dataset.phase = String(index);
    phaseButtons.forEach((button, i) => button.setAttribute('aria-pressed', String(i === index)));
  }

  function layout() {
    const header = document.querySelector('.zion-header');
    const admin = document.getElementById('wpadminbar');
    topInset = Math.ceil((header?.getBoundingClientRect().height ?? 76) + (admin?.getBoundingClientRect().height ?? 0) + 24);
    root.style.setProperty('--zion-forge-top', `${topInset}px`);
    root.style.setProperty('--zion-forge-height', `${Math.max(380, innerHeight - topInset - 16)}px`);
    for (const scene of scenes) {
      scene.rect = scene.host.getBoundingClientRect();
      // The hero canvas occupies only the illustration area, not the copy below.
      const { width, height } = scene.canvas.getBoundingClientRect();
      const ratio = Math.min(devicePixelRatio || 1, compact ? 1 : 1.5);
      const w = Math.round(width * ratio), h = Math.round(height * ratio);
      if (scene.canvas.width !== w || scene.canvas.height !== h) {
        scene.canvas.width = w; scene.canvas.height = h;
      }
      scene.width = width; scene.height = height;
      scene.context.setTransform(ratio, 0, 0, ratio, 0, 0);
      draw(scene);
    }
    onScroll();
  }

  function onScroll() {
    for (const scene of scenes) if (!scene.forge) scene.rect = scene.host.getBoundingClientRect();
    if (home && !reduced() && !paused) {
      for (const scene of scenes) {
        const rect = scene.section.getBoundingClientRect();
        scene.scroll = clamp((innerHeight - rect.top) / (innerHeight + rect.height));
      }
      const process = scenes.find(scene => scene.type === 'home-process');
      if (process) process.section.dataset.continuumStep = String(Math.min(2, Math.floor(process.scroll * 3)));
      schedule();
    }
    if (!forge || reduced()) return;
    const rect = forge.section.getBoundingClientRect();
    scrollProgress = clamp((topInset - rect.top) / Math.max(1, rect.height - (innerHeight - topInset - 16))) * 3;
    // Explicit stage selection stays until the visitor actually scrolls again.
    if (!paused) forge.selected = null;
    schedule();
  }

  function draw(scene) {
    if (home) { drawHome(scene); return; }
    const { context: ctx, width: w, height: h, forge: isForge } = scene;
    if (!w || !h) return;
    const progress = scene.progress;
    const layers = smooth(progress) * (1 - smooth(progress - 1));
    const solid = smooth(progress - 1) * (1 - smooth(progress - 2));
    const code = smooth(progress - 2);
    const pointer = reduced() ? [0, 0] : scene.pointer;
    const t = reduced() ? 0 : clock;
    const ry = -.38 + Math.sin(t * .23) * .12 + pointer[0] * .25 + code * .25;
    const rx = -.17 + Math.cos(t * .17) * .055 - pointer[1] * .18;
    const scale = Math.min(w * (isForge ? .24 : .32), h * (isForge ? .32 : .31));
    const cy = isForge ? h * .48 : h * .43;
    const project = projector(w, scale, rx, ry, cy);
    const stroke = (coords, alpha = 1, lineWidth = 1, close = false) => {
      trace(ctx, coords, alpha, lineWidth, close);
    };
    ctx.clearRect(0, 0, w, h);
    ctx.strokeStyle = palette.muted;
    // Drafting field and dimensional ticks establish depth without a bitmap.
    const field = Math.min(w * .41, h * .43);
    const ellipse = (radius, alpha, squish = .36) => {
      ctx.globalAlpha = alpha; ctx.lineWidth = 1; ctx.beginPath();
      ctx.ellipse(w / 2, cy + scale * .8, radius, radius * squish, -.12, 0, Math.PI * 2); ctx.stroke();
    };
    ellipse(field, .2); ellipse(field * .8, .12);
    for (let i = 0; i < 20; i++) {
      const a = i * Math.PI / 10;
      const x = w / 2 + Math.cos(a) * field;
      const y = cy + scale * .8 + Math.sin(a) * field * .36;
      stroke([[x, y - 3], [x, y + 3]], .3);
    }
    ctx.strokeStyle = palette.accent;
    // A segmented extrusion unfolds into individual manufacturing layers.
    for (let layer = 0; layer < 9; layer++) {
      const z = (layer - 4) * (.105 + layers * .15);
      const outline = polygon.map(([x, y]) => project(x, y, z));
      if (solid > .1 && layer === 0) {
        ctx.fillStyle = palette.accent; ctx.globalAlpha = solid * .11; ctx.beginPath();
        outline.forEach(([x, y], i) => i ? ctx.lineTo(x, y) : ctx.moveTo(x, y)); ctx.closePath(); ctx.fill();
      }
      stroke(outline, (.12 + layers * .4 + solid * .25) * (1 - code), layer === 0 || layer === 8 ? 1.2 : .65, true);
    }
    polygon.forEach(([x, y]) => stroke([project(x, y, -.42), project(x, y, .42)], .45 * solid * (1 - code)));
    // One bright plane travels through the build, revealing the volume inside.
    const scanZ = Math.sin(t * .42) * .48;
    stroke(polygon.map(([x, y]) => project(x, y, scanZ)), .75 * (1 - code), 1.4, true);
    const projected = points.map(point => {
      let x = point.x, y = point.y, z = point.z * (.06 + smooth(progress) * .94) + layers * (point.layer - 4) * .15;
      x = mix(x, point.code[0], code); y = mix(y, point.code[1], code); z = mix(z, point.code[2], code);
      return project(x, y, z);
    });
    if (code > .02) {
      ctx.strokeStyle = palette.accent;
      for (let i = 0; i < projected.length - 21; i += 5) {
        const a = projected[i], b = projected[i + 21];
        const distance = Math.hypot(a[0] - b[0], a[1] - b[1]);
        if (distance < scale * .75) stroke([a, [b[0], a[1]], b], .18 * code);
      }
    }
    const sweep = Math.sin(t * .42) * 1.3;
    projected.forEach(([x, y, depth], i) => {
      const point = points[i];
      const light = Math.max(0, 1 - Math.abs(point.y - sweep) * 4);
      ctx.globalAlpha = clamp(.32 + light * .5 + point.weight * .2 - depth * .1, .2, 1);
      ctx.fillStyle = i % 7 === 0 ? palette.text : palette.accent;
      const radius = .65 + point.weight * .85 + light * .3;
      ctx.fillRect(x, y, radius, radius);
    });
    // Sparse technical annotations remain decorative; editorial text stays HTML.
    ctx.fillStyle = palette.muted; ctx.globalAlpha = .65;
    ctx.font = `${compact ? 9 : 10}px monospace`;
    if (isForge) {
      ctx.fillText('Z / CONTINUUM', 18, h - 20);
      ctx.textAlign = 'right';
      ctx.fillText(['01 — SCHIȚĂ', '02 — STRATURI', '03 — OBIECT', '04 — COD'][Math.round(progress)], w - 18, h - 20);
      ctx.textAlign = 'left';
    }
    // Photons trace the drafting orbit; no flash, random flicker or large blur.
    for (let i = 0; i < 3; i++) {
      const a = t * .22 + i * Math.PI * 2 / 3;
      const x = w / 2 + Math.cos(a) * field;
      const y = cy + scale * .8 + Math.sin(a) * field * .36;
      ctx.fillStyle = palette.accent; ctx.globalAlpha = .9; ctx.beginPath();
      ctx.arc(x, y, 2, 0, Math.PI * 2); ctx.fill();
      ctx.globalAlpha = .1; ctx.beginPath(); ctx.arc(x, y, 8, 0, Math.PI * 2); ctx.fill();
    }
    if (code > .1) {
      ctx.fillStyle = palette.accent; ctx.globalAlpha = code * .8; ctx.font = `${compact ? 14 : 18}px monospace`;
      for (let i = 0; i < 6; i++) {
        const p = projected[i * 37]; ctx.fillText(['{ }', '</>', 'Z', '01', '→', '[ ]'][i], p[0], p[1]);
      }
    }
    ctx.globalAlpha = 1;
    if (!isForge) {
      scene.host.style.setProperty('--zion-note-rx', `${-pointer[1] * 3}deg`);
      scene.host.style.setProperty('--zion-note-ry', `${pointer[0] * 4}deg`);
    }
  }

  function projector(width, scale, rx, ry, cy) {
    return (x, y, z = 0) => {
      const a = x * Math.cos(ry) + z * Math.sin(ry), b = z * Math.cos(ry) - x * Math.sin(ry);
      const c = y * Math.cos(rx) - b * Math.sin(rx), depth = y * Math.sin(rx) + b * Math.cos(rx);
      const perspective = 3.8 / (3.8 + depth);
      return [width / 2 + a * scale * perspective, cy + c * scale * perspective, depth];
    };
  }
  function trace(ctx, coords, alpha = 1, width = 1, close = false) {
    if (!coords.length) return;
    ctx.globalAlpha = alpha; ctx.lineWidth = width; ctx.beginPath();
    coords.forEach(([x, y], i) => i ? ctx.lineTo(x, y) : ctx.moveTo(x, y));
    if (close) ctx.closePath();
    ctx.stroke();
  }

  function drawHome(scene) {
    const { context: ctx, width: w, height: h, type } = scene;
    if (!w || !h) return;
    const t = reduced() ? 0 : clock;
    const physical = type === 'home-physical';
    const ink = physical ? palette.ink : palette.accent;
    const muted = physical ? palette.ink : palette.muted;
    const pointer = reduced() ? [0, 0] : scene.pointer;
    const scroll = reduced() ? .5 : scene.scroll;
    const scale = Math.min(w * .32, h * .32);
    const project = projector(w, scale, -.2 + pointer[1] * .12, -.48 + Math.sin(t * .16) * .12 + pointer[0] * .2, h * .45);
    ctx.clearRect(0, 0, w, h); ctx.strokeStyle = ink; ctx.fillStyle = ink;
    const line = (coords, alpha = .5, width = 1, close = false) => trace(ctx, coords, alpha, width, close);
    const dot = (p, radius = 2.2, alpha = 1) => {
      ctx.fillStyle = ink; ctx.globalAlpha = alpha; ctx.beginPath(); ctx.arc(p[0], p[1], radius, 0, Math.PI * 2); ctx.fill();
      ctx.globalAlpha = alpha * .1; ctx.beginPath(); ctx.arc(p[0], p[1], radius * 3.5, 0, Math.PI * 2); ctx.fill();
    };
    const photon = (a, b, offset = 0, bend = false) => {
      const phase = (t * .17 + offset) % 1;
      const dx = Math.abs(b[0] - a[0]), dy = Math.abs(b[1] - a[1]);
      const split = dx / (dx + dy || 1);
      const position = !bend ? [mix(a[0], b[0], phase), mix(a[1], b[1], phase)] :
        phase < split ? [mix(a[0], b[0], phase / split), a[1]] : [b[0], mix(a[1], b[1], (phase - split) / (1 - split || 1))];
      dot(position, 2, .9);
    };
    const wire = (vertices, z, opacity) => line(vertices.map(([x, y]) => project(x, y, z)), opacity, 1, true);
    const label = (text, x, y, align = 'left') => {
      ctx.fillStyle = muted; ctx.globalAlpha = .75; ctx.font = `${compact ? 9 : 10}px monospace`; ctx.textAlign = align; ctx.fillText(text, x, y); ctx.textAlign = 'left';
    };

    if (type === 'home-hero') {
      // The manufacturing core unfolds into nine structural cross-sections.
      const spread = .11 + scroll * .1;
      const rings = [];
      for (let layer = 0; layer < 9; layer++) {
        const y = (layer - 4) * spread;
        const radius = .7 + .28 * Math.cos((layer - 4) * .4);
        const ring = Array.from({length: 24}, (_, i) => {
          const a = i * Math.PI / 12 + Math.PI / 12;
          return project(Math.cos(a) * radius, y, Math.sin(a) * radius);
        });
        rings.push(ring); line(ring, layer === 0 || layer === 8 ? .7 : .22, 1, true);
      }
      for (let i = 0; i < 24; i += 3) line(rings.map(ring => ring[i]), .32);
      const vertices = [[0,-1.1,0],[.75,0,0],[0,0,.75],[-.75,0,0],[0,0,-.75],[0,1.1,0]].map(v => project(...v));
      for (const end of [0,5]) for (let i = 1; i < 5; i++) { line([vertices[end],vertices[i]], .75); photon(vertices[end],vertices[i], i / 4); }
      line([vertices[1],vertices[2],vertices[3],vertices[4]], .6, 1, true);
      const sweep = (Math.sin(t * .5) + 1) / 2 * 8;
      line(rings[Math.floor(sweep)], .95, 1.5, true);
      for (let i = 0; i < (compact ? 80 : 170); i++) {
        const point = points[i];
        const a = point.weight * Math.PI * 2;
        const radius = .9 + point.z * .7;
        dot(project(Math.cos(a) * radius, point.y * .85, Math.sin(a) * radius), .65, .6);
      }
      ctx.strokeStyle = muted;
      line([[18,h*.25],[w*.15,h*.25],[w*.23,h*.4]], .35);
      line([[w-18,h*.65],[w*.85,h*.65],[w*.76,h*.5]], .35);
      label('SCHIȚĂ / STRATURI / OBIECT', 18, h - 46);
      label('ZION / NUCLEU CREATIV', 18, 22);
    } else if (physical) {
      const shape = [[-1,-.9],[-.45,-.9],[-.45,.4],[1,.4],[1,.95],[-1,.95]];
      for (let layer = 0; layer < 7; layer++) wire(shape, (layer - 3) * .14, .2 + layer * .055);
      const z = Math.sin(t * .45) * .42;
      wire(shape, z, .9);
      shape.forEach(([x,y]) => line([project(x,y,-.42),project(x,y,.42)], .4));
      dot(project(-.72, .6, z), 2.3);
      label('FABRICAȚIE / STRAT CU STRAT', 12, h - 18);
    } else if (type === 'home-digital') {
      const shape = [[-1,-.8],[1,-.8],[1,.8],[-1,.8]];
      for (let layer = 0; layer < 3; layer++) {
        const z = (layer - 1) * .45; wire(shape, z, .25 + layer * .15);
        for (let row = 0; row < 5; row++) {
          const y = -.5 + row * .22;
          const a = project(-.72,y,z), b = project(.2 + (row % 3) * .18,y,z);
          line([a,b], .3); if (layer === 2) photon(a,b,row / 5);
        }
      }
      const a = project(-1,-.8,-.45), b = project(1,.8,.45);
      line([a,[b[0],a[1]],b], .55); dot(a); dot(b);
      label('SOFTWARE / CONEXIUNI UTILE', 12, h - 18);
    } else if (type === 'home-services' || type === 'home-process') {
      const count = type === 'home-services' ? 7 : 3;
      const xInset = w * (compact ? .07 : .09), y = h * .48;
      const nodes = Array.from({length:count}, (_, i) => [mix(xInset,w-xInset,i/(count-1)),y+(i%2 ? -.16 : .12)*h]);
      for (let i = 0; i < count - 1; i++) {
        line([nodes[i],[nodes[i+1][0],nodes[i][1]],nodes[i+1]], .42);
        photon(nodes[i],nodes[i+1],i / count,true);
        if (count === 7 && i < count - 2) line([nodes[i],nodes[i+2]], .14);
      }
      nodes.forEach((p,i) => {
        const active = count === 7 ? scene.active === i+1 : Math.floor(scroll * 3) === i;
        const radius = count === 7 ? (compact ? 4 : 7) : (compact ? 9 : 15);
        ctx.strokeStyle = ink;
        line([[p[0],p[1]-radius],[p[0]+radius,p[1]],[p[0],p[1]+radius],[p[0]-radius,p[1]]],active ? 1 : .55,active ? 1.5 : 1,true);
        dot(p, active ? 3 : 1.5, active ? 1 : .6);
        label(count === 7 ? `0${i+1}` : ['IDEE','DIRECȚIE','REALITATE'][i],p[0],p[1]+radius+24,'center');
      });
      if (count === 7) label('7 DISCIPLINE / UN SINGUR FIR',12,18);
    } else if (type === 'home-portal') {
      const centre = [w * .5,h * .4];
      for (let i = 0; i < 11; i++) {
        const a = [w * i / 10, h * .05], b = [w * (1-i/10),h * .77];
        line([a,centre,b], .1 + (i%3)*.05);
        if (i % 2 === 0) photon(a,centre,i/11);
      }
      const r = Math.min(w*.12,h*.16);
      line([[centre[0],centre[1]-r],[centre[0]+r,centre[1]],[centre[0],centre[1]+r],[centre[0]-r,centre[1]]],.8,1.2,true);
      dot(centre,3,.9); label('URMĂTOAREA IDEE / A TA',12,h-18);
    }
    ctx.globalAlpha = 1;
  }

  function tick(now) {
    raf = 0;
    if (paused || reduced() || document.hidden || !scenes.some(scene => scene.visible)) { last = 0; return; }
    const elapsed = last ? now - last : (compact ? 42 : 34);
    if (elapsed < (compact ? 41 : 32)) { schedule(); return; }
    clock += Math.min(elapsed, 80) / 1000; last = now;
    for (const scene of scenes) {
      if (!scene.visible) continue;
      scene.pointer = scene.pointer.map((value, i) => mix(value, scene.aim[i], .1));
      if (scene.forge && scene.selected === null) {
        scene.progress = mix(scene.progress, scrollProgress, .14);
        updatePhase(Math.round(scene.progress));
      }
      draw(scene);
    }
    schedule();
  }
  function schedule() {
    if (!raf && !paused && !reduced() && !document.hidden && scenes.some(scene => scene.visible)) raf = requestAnimationFrame(tick);
  }
  function preferences() {
    const staticMode = reduced();
    root.classList.toggle('is-spatial-static', staticMode);
    pauseButtons.forEach(button => { button.hidden = staticMode; });
    scenes.forEach(scene => {
      scene.canvas.dataset.renderMode = staticMode ? 'static' : 'animated';
      if (staticMode) { scene.pointer = [0, 0]; scene.progress = 2; }
      draw(scene);
    });
    if (staticMode) updatePhase(2);
    if (staticMode) { cancelAnimationFrame(raf); raf = 0; last = 0; }
    else schedule();
    layout();
  }
  root.classList.add('is-spatial');
  updatePhase(0);
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      const scene = scenes.find(item => item.canvas === entry.target);
      if (scene) scene.visible = entry.isIntersecting;
    });
    schedule();
  });
  const resize = new ResizeObserver(layout);
  scenes.forEach(scene => { observer.observe(scene.canvas); resize.observe(scene.host); });
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', layout, { passive: true });
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) { cancelAnimationFrame(raf); raf = 0; last = 0; } else schedule();
  });
  motion.addEventListener('change', preferences);
  new MutationObserver(preferences).observe(document.body, { attributes: true, attributeFilter: ['class'] });
  preferences();
})();
