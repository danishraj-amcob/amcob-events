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
})();
