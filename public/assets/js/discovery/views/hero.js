import { ICONS, MOA, WD } from '../config.js';
import {
  escapeHtml,
  eventHref,
  imageUrl,
  parseDate,
  thumbGradient,
  thumbImg,
} from '../utils.js';

export function initHero(store) {
  let featured = (store.featured || []).filter((e) => e.status === 'upcoming' || e.status === 'live');
  if (!featured.length) {
    featured = store.events.filter((e) => e.featured && (e.status === 'upcoming' || e.status === 'live'));
  }
  if (!featured.length) {
    featured = store.events.filter((e) => e.status === 'upcoming').slice(0, 5);
  } else {
    featured = featured.slice(0, 5);
  }

  const slidesHost = document.getElementById('heroSlides');
  const dotsHost = document.getElementById('heroDots');
  const heroRoot = document.getElementById('dxHero');
  const slider = heroRoot ? heroRoot.querySelector('.heroslider') : null;
  if (!slidesHost || !dotsHost) return;

  const next = document.getElementById('hsNext');
  const prev = document.getElementById('hsPrev');

  function setControlsHidden(hidden) {
    [next, prev, dotsHost].forEach((el) => {
      if (!el) return;
      el.classList.toggle('is-hidden', hidden);
      if (hidden) el.setAttribute('hidden', '');
      else el.removeAttribute('hidden');
    });
    if (slider) slider.classList.toggle('is-empty', hidden);
  }

  if (!featured.length) {
    if (heroRoot) heroRoot.style.display = '';
    if (!document.getElementById('heroEmpty')) {
      slidesHost.innerHTML =
        '<div class="slide slide-empty active" id="heroEmpty">' +
          '<div class="sbg" aria-hidden="true"></div>' +
          '<div class="sov" aria-hidden="true"></div>' +
          '<div class="sin">' +
            '<span class="seye"><span class="sdot"></span>Mixers &middot; Workshops &middot; Conferences</span>' +
            '<h2>Where the chapter meets</h2>' +
            '<p class="slead">Dinner mixers, networking nights and workshops across every AMCOB chapter.</p>' +
            '<div class="scta"><a class="btn btn-gold" href="/get-the-app">Get the app</a><a class="btn btn-glass" href="#events">See the calendar</a></div>' +
          '</div>' +
        '</div>';
    }
    dotsHost.innerHTML = '';
    setControlsHidden(true);
    return;
  }

  setControlsHidden(false);

  function slideHTML(e) {
    const d = parseDate(e.date);
    const src = imageUrl(e, 1500);
    const bg = src
      ? thumbImg(src, e.title, 'sbg')
      : '<div class="sbg" style="background:' + escapeHtml(thumbGradient(e)) + '"></div>';
    return '<a class="slide" href="' + escapeHtml(eventHref(e)) + '">' +
      bg +
      '<div class="sov"></div>' +
      '<div class="sin">' +
        '<span class="seye"><span class="sdot"></span>Featured &middot; ' + escapeHtml(e.cat) + '</span>' +
        '<h2>' + escapeHtml(e.title) + '</h2>' +
        '<div class="smeta">' +
          '<div>' + ICONS.cal + WD[d.getDay()].slice(0, 3) + ', ' + MOA[d.getMonth()] + ' ' + d.getDate() + ' &middot; ' + escapeHtml(e.time) + '</div>' +
          '<div>' + ICONS.pin + escapeHtml(e.loc) + '</div>' +
          '<div><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>' + escapeHtml(e.price || 'Free') + '</div>' +
        '</div>' +
        '<div class="scta"><span class="btn btn-gold">Register now</span><span class="btn btn-glass">View details</span></div>' +
      '</div>' +
    '</a>';
  }

  slidesHost.innerHTML = featured.map(slideHTML).join('');
  dotsHost.innerHTML = featured.map((_, i) => '<i data-i="' + i + '"></i>').join('');
  const slideEls = Array.prototype.slice.call(slidesHost.children);
  const dotEls = Array.prototype.slice.call(dotsHost.children);
  let hi = 0;
  let heroTimer;
  const single = featured.length < 2;

  if (single) {
    if (next) next.classList.add('is-hidden');
    if (prev) prev.classList.add('is-hidden');
    dotsHost.classList.add('is-hidden');
  }

  function showSlide(i) {
    hi = (i + slideEls.length) % slideEls.length;
    slideEls.forEach((s, k) => s.classList.toggle('active', k === hi));
    dotEls.forEach((d, k) => d.classList.toggle('on', k === hi));
  }
  function heroAuto() {
    clearInterval(heroTimer);
    if (single) return;
    heroTimer = setInterval(() => showSlide(hi + 1), 5500);
  }
  function heroGo(i) { showSlide(i); heroAuto(); }

  if (!single) {
    if (next) next.addEventListener('click', () => heroGo(hi + 1));
    if (prev) prev.addEventListener('click', () => heroGo(hi - 1));
    dotEls.forEach((d) => d.addEventListener('click', () => heroGo(+d.getAttribute('data-i'))));
  }
  showSlide(0);
  heroAuto();
}
