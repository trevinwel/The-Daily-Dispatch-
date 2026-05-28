'use strict';

const searchInput   = document.getElementById('search-input');
const searchResults = document.getElementById('search-results');
let searchTimer = null;

if (searchInput) {
  const wrap = searchInput.closest('.header-search-wrap') || searchInput.parentElement.parentElement;

  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    const q = searchInput.value.trim();
    if (q.length < 2) {
      if (searchResults) searchResults.innerHTML = '';
      return;
    }
    searchTimer = setTimeout(async () => {
      try {
        const res  = await fetch('/newssite/api/search.php?q=' + encodeURIComponent(q), { credentials: 'same-origin' });
        const data = await res.json();
        renderSearch(data.results);
      } catch (err) {}
    }, 300);
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.header-search-wrap')) {
      if (searchResults) searchResults.innerHTML = '';
    }
  });
}

function renderSearch(results) {
  if (!searchResults) return;
  if (!results || results.length === 0) {
    searchResults.innerHTML = '<p class="no-results">No articles found.</p>';
    return;
  }
  searchResults.innerHTML = results.map(a =>
    '<a class="search-result-item" href="/newssite/article.php?id=' + a.id + '">' +
    (a.image_path
      ? '<img class="search-thumb" src="' + a.image_path + '" alt="">'
      : '<div class="search-thumb-placeholder"></div>') +
    '<div class="search-result-text"><strong>' + a.headline + '</strong><span class="stag">' + a.category_name + '</span></div></a>'
  ).join('');
}

document.querySelectorAll('.cat-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const slug = btn.dataset.slug;
    window.location.href = slug ? '?category=' + encodeURIComponent(slug) : '?';
  });
});