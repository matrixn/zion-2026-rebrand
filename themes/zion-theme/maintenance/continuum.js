/* Zion Continuum / maintenance 1.0.0. Original Canvas2D geometry, no dependencies. */
(() => {
  'use strict';
  const root = document.querySelector('[data-zion-maintenance]');
  if (!root) return;
  const canvas = root.querySelector('canvas'), scene = root.querySelector('.zion-uc__scene');
  let ctx;
  try { ctx = canvas.getContext('2d'); } catch { return; }
  if (!ctx) return;
  const button = root.querySelector('.zion-uc__motion'), label = button.querySelector('span');
  const motion = matchMedia('(prefers-reduced-motion: reduce)');
  const colors = getComputedStyle(root), lime = colors.getPropertyValue('--uc-accent').trim();
  const muted = colors.getPropertyValue('--uc-muted').trim();
  const surface = colors.getPropertyValue('--uc-surface').trim();
  let width = 0, height = 0, size = 0, frame = 0, last = 0, phase = 0;
  let paused = false, visible = true, targetX = 0, targetY = 0, pointerX = 0, pointerY = 0;
  const project = (x,y,z,angle) => {
    const a = angle + .55, b = -.38 + pointerY * .1;
    const xx = x * Math.cos(a) + z * Math.sin(a);
    const zz = -x * Math.sin(a) + z * Math.cos(a);
    const yy = y * Math.cos(b) - zz * Math.sin(b);
    const depth = y * Math.sin(b) + zz * Math.cos(b);
    const perspective = 3.8 / (3.8 + depth);
    return [width * .51 + xx * size * perspective, height * .5 + yy * size * perspective, depth];
  };
  const stroke = (points, color, alpha = 1, weight = 1, close = false) => {
    ctx.beginPath(); points.forEach((p,i) => i ? ctx.lineTo(p[0],p[1]) : ctx.moveTo(p[0],p[1]));
    if (close) ctx.closePath(); ctx.strokeStyle = color; ctx.globalAlpha = alpha; ctx.lineWidth = weight; ctx.stroke();
  };
  const draw = () => {
    ctx.clearRect(0,0,width,height);
    const angle = phase * .09 + pointerX * .1;
    // Ground plane: a CAD construction grid, projected into the same scene.
    for (let i = -5; i <= 5; i++) {
      stroke([project(i*.28,.91,-1.4,angle),project(i*.28,.91,1.4,angle)], muted, .08);
      stroke([project(-1.4,.91,i*.28,angle),project(1.4,.91,i*.28,angle)], muted, .08);
    }
    // Eleven fabrication slices twist into a coherent object, then softly unfold.
    const layers = Array.from({length:11},(_,i) => {
      const y = (i-5)*.115;
      const radius = .68 + Math.cos((i-5)*.37)*.17;
      const twist = Math.sin(phase*.26 + i*.12)*.25;
      const lift = Math.sin(phase*.37)*.025 * (i-5);
      const points = Array.from({length:6},(_,j) => {
        const a = j*Math.PI/3 + twist;
        return project(Math.cos(a)*radius,y+lift,Math.sin(a)*radius,angle);
      });
      return {points,depth:project(0,y,0,angle)[2],i};
    }).sort((a,b)=>b.depth-a.depth);
    for (const layer of layers) {
      const {points,i} = layer;
      ctx.fillStyle = surface; ctx.globalAlpha = .64; ctx.beginPath();
      points.forEach((p,j)=>j?ctx.lineTo(p[0],p[1]):ctx.moveTo(p[0],p[1])); ctx.closePath(); ctx.fill();
      stroke(points,lime,i===5?.85:.21+ i*.018,i===5?1.6:.8,true);
      for(let j=0;j<6;j++) {
        const p=points[j]; ctx.fillStyle=lime;ctx.globalAlpha=.55;ctx.fillRect(p[0]-1,p[1]-1,2,2);
      }
      // Light packets follow the exact perimeter, with a short luminous tail.
      const travel = ((phase*.19+i*.073)%1)*6, edge=Math.floor(travel), fraction=travel-edge;
      const start=points[edge], end=points[(edge+1)%6];
      const x=start[0]+(end[0]-start[0])*fraction,y=start[1]+(end[1]-start[1])*fraction;
      ctx.globalAlpha=.85;ctx.fillStyle=lime;ctx.shadowColor=lime;ctx.shadowBlur=10;
      ctx.beginPath();ctx.arc(x,y,i%3===0?2:1.25,0,Math.PI*2);ctx.fill();ctx.shadowBlur=0;
    }
    // Datum guides and quiet engineering annotations.
    stroke([[width*.13,height*.18],[width*.13,height*.8]],muted,.18);
    stroke([[width*.1,height*.18],[width*.16,height*.18]],lime,.4);
    stroke([[width*.1,height*.8],[width*.16,height*.8]],lime,.4);
    stroke([[width*.25,height*.86],[width*.8,height*.86]],muted,.18);
    for(let i=0;i<7;i++) {
      const x=width*(.25+i*.55/6);stroke([[x,height*.85],[x,height*.87]],muted,.3);
    }
    ctx.globalAlpha = 1;
    canvas.dataset.renderMode = motion.matches || paused ? 'static' : 'animated';
  };
  const running = () => !paused && !motion.matches && visible && !document.hidden;
  const tick = now => {
    frame=0; if(!running()) return;
    const interval=width<500?1000/24:1000/30;
    if(now-last>=interval) {
      // Advance by elapsed active time only; a hidden tab never catches up.
      phase+=Math.min((now-last)/1000,.06);last=now;
      pointerX+=(targetX-pointerX)*.045;pointerY+=(targetY-pointerY)*.045;draw();
    }
    frame=requestAnimationFrame(tick);
  };
  const schedule = () => {
    if(frame) cancelAnimationFrame(frame); frame=0; last=performance.now();
    button.hidden=motion.matches;
    button.setAttribute('aria-pressed',String(paused));
    label.textContent=paused?'Pornește animația':'Oprește animația';
    draw();if(running())frame=requestAnimationFrame(tick);
  };
  const resize = () => {
    const box=scene.getBoundingClientRect();width=box.width;height=box.height;size=Math.min(width,height)*.34;
    const dpr=Math.min(devicePixelRatio||1,1.75);
    canvas.width=Math.round(width*dpr);canvas.height=Math.round(height*dpr);ctx.setTransform(dpr,0,0,dpr,0,0);schedule();
  };
  button.addEventListener('click',()=>{paused=!paused;schedule();});
  document.addEventListener('visibilitychange',schedule);
  motion.addEventListener('change',schedule);
  if(matchMedia('(hover:hover) and (pointer:fine)').matches) {
    scene.addEventListener('pointermove',event=>{
      const box=scene.getBoundingClientRect();targetX=(event.clientX-box.left)/box.width-.5;targetY=(event.clientY-box.top)/box.height-.5;
    },{passive:true});
    scene.addEventListener('pointerleave',()=>{targetX=0;targetY=0;});
  }
  if('IntersectionObserver' in window)new IntersectionObserver(entries=>{visible=entries[0].isIntersecting;schedule();},{threshold:.01}).observe(scene);
  if('ResizeObserver' in window)new ResizeObserver(resize).observe(scene);
  else window.addEventListener('resize',resize,{passive:true});
  resize();root.classList.add('is-ready');
})();
