(() => {
  'use strict';

  // Mobile nav toggle
  const hamburger = document.getElementById('hamburger');
  const mainNav = document.getElementById('main-nav');

  if (hamburger && mainNav) {
    hamburger.addEventListener('click', () => {
      const isOpen = mainNav.classList.toggle('is-open');
      hamburger.setAttribute('aria-expanded', String(isOpen));
      hamburger.classList.toggle('is-open', isOpen);
    });

    mainNav.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        mainNav.classList.remove('is-open');
        hamburger.setAttribute('aria-expanded', 'false');
      });
    });
  }

  // FAQ accordion
  document.querySelectorAll('.faq-item').forEach(item => {
    const btn = item.querySelector('.faq-q');
    btn.addEventListener('click', () => {
      const isOpen = item.classList.contains('is-open');
      item.parentElement.querySelectorAll('.faq-item').forEach(el => {
        el.classList.remove('is-open');
        el.querySelector('.faq-q').setAttribute('aria-expanded', 'false');
      });
      if (!isOpen) {
        item.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });

  // Newsletter form
  const form = document.getElementById('subscribe-form');
  const note = document.getElementById('form-note');
  if (form) {
    form.addEventListener('submit', e => {
      e.preventDefault();
      const email = form.email.value.trim();
      if (email) {
        note.textContent = `Thanks — we'll send updates to ${email}.`;
        form.reset();
      }
    });
  }

  // Hide header on scroll down, slide it back in on scroll up
  const header = document.querySelector('.site-header');
  if (header) {
    let lastScrollY = window.scrollY;
    let ticking = false;

    const updateHeader = () => {
      const currentScrollY = window.scrollY;

      if (currentScrollY <= 0) {
        header.classList.remove('header-hidden');
      } else if (currentScrollY > lastScrollY) {
        header.classList.add('header-hidden');
      } else if (currentScrollY < lastScrollY) {
        header.classList.remove('header-hidden');
      }

      lastScrollY = currentScrollY;
      ticking = false;
    };

    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(updateHeader);
        ticking = true;
      }
    }, { passive: true });
  }

  // Back to top button
  const toTop = document.getElementById('to-top');
  if (toTop) {
    window.addEventListener('scroll', () => {
      toTop.classList.toggle('is-visible', window.scrollY > 700);
    }, { passive: true });
    toTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Line-draw animation for sparkline SVGs (replays every time each one enters view)
  document.querySelectorAll('.line-draw-path').forEach(path => {
    const length = path.getTotalLength();
    path.style.strokeDasharray = String(length);

    const reset = () => {
      path.style.transition = 'none';
      path.style.strokeDashoffset = String(length);
      path.getBoundingClientRect(); // force reflow so the reset paints before re-enabling the transition
      path.style.transition = '';
    };
    reset();

    const target = path.closest('.floating-card') || path.closest('li') || path;
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // double rAF: let the reset (dashoffset = length) paint first, then animate to 0
          requestAnimationFrame(() => {
            requestAnimationFrame(() => {
              path.style.strokeDashoffset = '0';
            });
          });
        } else {
          reset();
        }
      });
    }, { threshold: 0.4 });

    observer.observe(target);
  });

  // Line-reveal wipe for raster sparkline icons (same "draws itself in" effect, via clip-path
  // since there's no path data to animate stroke-dashoffset on a PNG)
  document.querySelectorAll('.line-reveal-img').forEach(img => {
    const reset = () => {
      img.style.transition = 'none';
      img.style.clipPath = 'inset(0 100% 0 0)';
      img.getBoundingClientRect(); // force reflow so the reset paints before re-enabling the transition
      img.style.transition = '';
    };
    reset();

    const target = img.closest('li') || img;
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          requestAnimationFrame(() => {
            requestAnimationFrame(() => {
              img.style.clipPath = 'inset(0 0 0 0)';
            });
          });
        } else {
          reset();
        }
      });
    }, { threshold: 0.4 });

    observer.observe(target);
  });

  // Number counters (count up once when the stat scrolls into view)
  document.querySelectorAll('.counter').forEach(el => {
    const target = parseFloat(el.dataset.target);
    if (Number.isNaN(target)) return;

    const duration = 1500;
    const animate = () => {
      const start = performance.now();
      const step = now => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(target * eased);
        if (progress < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animate();
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    observer.observe(el);
  });

  // Staggered scroll reveal for simple showcase groups (e.g. benefits)
  document.querySelectorAll('[data-reveal-group]').forEach(group => {
    group.classList.add('reveal-js');
    const observer = new IntersectionObserver(entries => {
      if (!entries.some(entry => entry.isIntersecting)) return;
      observer.disconnect();
      group.classList.add('is-revealed');
      // drop the reveal transitions so hover transitions take over again
      setTimeout(() => group.classList.add('is-settled'), 1600);
    }, { threshold: 0.2 });
    observer.observe(group);
  });

  // Hero: gentle pointer parallax on the phone + floating cards (desktop only,
  // skipped when reduced motion is requested). Uses the independent `translate`
  // property so it layers on top of the CSS entrance/float animations.
  const heroInner = document.querySelector('[data-hero]');
  if (heroInner
    && window.matchMedia('(hover: hover) and (pointer: fine)').matches
    && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const layers = Array.from(heroInner.querySelectorAll('[data-depth]'))
      .map(el => ({ el, depth: parseFloat(el.dataset.depth) || 0 }));
    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    let raf = null;

    const render = () => {
      currentX += (targetX - currentX) * 0.08;
      currentY += (targetY - currentY) * 0.08;
      layers.forEach(({ el, depth }) => {
        el.style.translate = `${(currentX * depth).toFixed(2)}px ${(currentY * depth).toFixed(2)}px`;
      });
      raf = Math.abs(targetX - currentX) > 0.001 || Math.abs(targetY - currentY) > 0.001
        ? requestAnimationFrame(render)
        : null;
    };

    const startRender = () => {
      if (!raf) raf = requestAnimationFrame(render);
    };

    heroInner.addEventListener('mousemove', e => {
      const rect = heroInner.getBoundingClientRect();
      targetX = ((e.clientX - rect.left) / rect.width - 0.5) * 2;
      targetY = ((e.clientY - rect.top) / rect.height - 0.5) * 2;
      startRender();
    }, { passive: true });

    heroInner.addEventListener('mouseleave', () => {
      targetX = 0;
      targetY = 0;
      startRender();
    });
  }

  // Features: sticky stacking cards. Each card eases in as it arrives (--a) and
  // recedes (scales down + dims via --p) while the next card slides over it.
  const featureStack = document.querySelector('[data-feature-stack]');
  if (featureStack) {
    const cards = Array.from(featureStack.querySelectorAll('.feature-card'));
    const stickyQuery = window.matchMedia('(min-width: 961px) and (min-height: 680px)');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const clamp01 = v => Math.min(Math.max(v, 0), 1);
    let restTops = [];
    let ticking = false;

    // where each card comes to rest: its sticky top, or ~35% down the screen when not pinned
    const measure = () => {
      restTops = cards.map(card => (stickyQuery.matches
        ? parseFloat(getComputedStyle(card).top) || 0
        : window.innerHeight * 0.35));
    };

    const update = () => {
      ticking = false;
      if (reduceMotion.matches) return;
      const vh = window.innerHeight;
      const arrived = cards.map((card, i) =>
        clamp01((vh - card.getBoundingClientRect().top) / Math.max(vh - restTops[i], 1)));

      cards.forEach((card, i) => {
        card.style.setProperty('--a', arrived[i].toFixed(3));
        const covered = stickyQuery.matches && i + 1 < cards.length ? arrived[i + 1] : 0;
        card.style.setProperty('--p', covered.toFixed(3));
      });
    };

    const requestUpdate = () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(update);
    };

    const remeasure = () => {
      measure();
      requestUpdate();
    };

    measure();
    update();
    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', remeasure);
    stickyQuery.addEventListener('change', remeasure);
  }

  // Active nav link on scroll
  const sections = ['community', 'features', 'benefits', 'how-it-works', 'faq']
    .map(id => document.getElementById(id))
    .filter(Boolean);
  const navLinks = document.querySelectorAll('.main-nav a');

  if (sections.length && navLinks.length) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.id;
          navLinks.forEach(link => {
            link.classList.toggle('is-active', link.getAttribute('href') === `#${id}`);
          });
        }
      });
    }, { rootMargin: '-40% 0px -55% 0px' });

    sections.forEach(section => observer.observe(section));
  }

  // Background-word cursor-following spotlight (desktop only), shared by any
  // section that has a .bg-word + .bg-word-stroke pair (currently #features
  // and #benefits). Custom properties are written to the shared .section-head
  // ancestor, not .bg-word itself, since the base text and stroke layer are
  // siblings and don't inherit from each other — only from a common ancestor.
  const setupBgWordSpotlight = sectionId => {
    const section = document.getElementById(sectionId);
    const bgWord = section ? section.querySelector('.bg-word') : null;
    const head = section ? section.querySelector('.section-head') : null;
    if (!section || !bgWord || !head) return;

    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    let raf = null;

    const render = () => {
      currentX += (targetX - currentX) * 0.18;
      currentY += (targetY - currentY) * 0.18;
      head.style.setProperty('--spot-x', `${currentX}px`);
      head.style.setProperty('--spot-y', `${currentY}px`);

      if (Math.abs(targetX - currentX) > 0.5 || Math.abs(targetY - currentY) > 0.5) {
        raf = requestAnimationFrame(render);
      } else {
        raf = null;
      }
    };

    const startRender = () => {
      if (!raf) raf = requestAnimationFrame(render);
    };

    section.addEventListener('mousemove', e => {
      const rect = bgWord.getBoundingClientRect();
      targetX = e.clientX - rect.left;
      targetY = e.clientY - rect.top;
      head.style.setProperty('--spot-opacity', '1');
      startRender();
    }, { passive: true });

    section.addEventListener('mouseleave', () => {
      head.style.setProperty('--spot-opacity', '0');
    });
  };

  // FAQ card border spotlight (desktop only): same cursor-following-glow
  // technique as the background-word spotlight above, applied per-card via
  // .faq-item's own --faq-spot-x/--faq-spot-y/--faq-spot-opacity instead of a
  // shared section ancestor, since each card tracks the cursor independently.
  const setupBorderSpotlight = item => {
    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    let raf = null;

    const render = () => {
      currentX += (targetX - currentX) * 0.2;
      currentY += (targetY - currentY) * 0.2;
      item.style.setProperty('--faq-spot-x', `${currentX}px`);
      item.style.setProperty('--faq-spot-y', `${currentY}px`);

      if (Math.abs(targetX - currentX) > 0.5 || Math.abs(targetY - currentY) > 0.5) {
        raf = requestAnimationFrame(render);
      } else {
        raf = null;
      }
    };

    const startRender = () => {
      if (!raf) raf = requestAnimationFrame(render);
    };

    item.addEventListener('mousemove', e => {
      const rect = item.getBoundingClientRect();
      targetX = e.clientX - rect.left;
      targetY = e.clientY - rect.top;
      item.style.setProperty('--faq-spot-opacity', '1');
      startRender();
    }, { passive: true });

    item.addEventListener('mouseleave', () => {
      item.style.setProperty('--faq-spot-opacity', '0');
    });
  };

  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    ['features', 'benefits'].forEach(setupBgWordSpotlight);
    document.querySelectorAll('.faq-item').forEach(setupBorderSpotlight);
  }
})();
