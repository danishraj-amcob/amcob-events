import { cardHtml, noResultHtml } from '../templates.js';

export function renderGrid(store) {
  const list = store.filtered();
  const grid = document.getElementById('egrid');
  if (!grid) return;
  grid.innerHTML = list.length ? list.map((e) => cardHtml(store, e)).join('') : noResultHtml();
}
