/* A local editorial demonstration. No requests, storage, analytics or personal data. */
(() => {
  const preference = matchMedia('(prefers-reduced-motion: reduce)');
  document.querySelectorAll('[data-learning-lab]').forEach(root => {
    const steps = [...root.querySelectorAll('[data-learning-step]')];
    const panels = [...root.querySelectorAll('[data-learning-panel]')];
    const status = root.querySelector('[data-learning-status]');
    const paint = root.querySelector('[data-learning-paint]');
    const answers = [...root.querySelectorAll('[data-learning-answer]')];
    const motion = root.querySelector('[data-learning-motion]');
    let paused = false;
    const updateMotion = () => {
      const reduced = preference.matches || document.body.classList.contains('zion-motion-reduced');
      root.classList.toggle('is-paused', paused || reduced || document.hidden);
      motion.disabled = reduced;
      motion.setAttribute('aria-pressed', String(paused || reduced));
      motion.textContent = reduced ? 'Mișcare redusă' : paused ? 'Pornește animația' : 'Pauză animație';
    };
    const select = value => {
      root.dataset.stage = value;
      steps.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.learningStep === value)));
      panels.forEach(panel => { panel.hidden = panel.dataset.learningPanel !== value; });
      status.textContent = { 1:'01 / Modelul prinde formă.', 2:'02 / Încearcă butonul de culoare.', 3:'03 / Alege răspunsul care leagă forma de o idee.' }[value];
    };
    root.classList.add('is-enhanced');
    root.querySelectorAll('button').forEach(button => { button.disabled = false; });
    steps.forEach(button => button.addEventListener('click', () => select(button.dataset.learningStep)));
    paint.addEventListener('click', () => {
      const painted = root.classList.toggle('is-painted');
      paint.setAttribute('aria-pressed', String(painted));
      paint.textContent = painted ? 'Revino la modelul alb' : 'Adaugă culoare';
      status.textContent = painted ? 'Accentul lime este activ. Într-un kit real alegi tu culorile.' : 'Modelul a revenit la aspectul alb.';
    });
    answers.forEach(button => button.addEventListener('click', () => {
      answers.forEach(answer => answer.setAttribute('aria-pressed', String(answer === button)));
      const correct = button.dataset.learningAnswer === 'correct';
      root.classList.toggle('is-discovered', correct);
      status.textContent = correct ? 'Exact. Numele se leagă de forma pe care o observi. Curiozitatea continuă.' : 'Mai încearcă: steagul identifică o componentă; ghidul rămâne parte din experiență.';
    }));
    root.querySelector('[data-learning-reset]').addEventListener('click', () => {
      root.classList.remove('is-painted', 'is-discovered');
      paint.setAttribute('aria-pressed', 'false');
      paint.textContent = 'Adaugă culoare';
      answers.forEach(button => button.setAttribute('aria-pressed', 'false'));
      select('1');
    });
    motion.addEventListener('click', () => { paused = !paused; updateMotion(); });
    preference.addEventListener('change', updateMotion);
    document.addEventListener('visibilitychange', updateMotion);
    new MutationObserver(updateMotion).observe(document.body, { attributes:true, attributeFilter:['class'] });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(entries => root.classList.toggle('is-visible', entries[0].isIntersecting), { threshold:0.05 }).observe(root);
    }
    select('1');
    updateMotion();
  });
})();
