<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/User.php';
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Article.php';

Auth::start();

$articleModel = new Article();
$categories   = $articleModel->getCategories();

$categorySlug = trim($_GET['category'] ?? '');
$categoryId   = null;
foreach ($categories as $cat) {
    if ($cat['slug'] === $categorySlug) {
        $categoryId = (int) $cat['id'];
        break;
    }
}

$articles = $articleModel->getAll(13, 0, $categoryId);
$hero     = !empty($articles) ? array_shift($articles) : null;
$user     = Auth::user();
$today    = date('l, F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>The Daily Dispatch — Sri Lanka News</title>
<link rel="stylesheet" href="/newssite/assets/css/main.css">
</head>
<body>

<div class="topbar">
  <div class="container">
    <span class="topbar-date"><?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?></span>
    <div class="topbar-links">
      <a href="/newssite/about.php">About</a>
      <a href="/newssite/contact.php">Contact</a>
      <?php if (!$user): ?>
      <a href="/newssite/register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="container">
    <a href="/newssite/" class="site-logo">Daily<span>Dispatch</span></a>

    <div style="position:relative;" class="header-search-wrap">
      <div class="header-search">
        <input type="search" id="search-input" placeholder="Search news…" autocomplete="off" maxlength="100">
        <button type="button">Search</button>
      </div>
      <div id="search-results" class="search-results-dropdown"></div>
    </div>

    <div class="header-right">
      <?php if ($user): ?>
        <div class="notif-wrapper">
          <button id="notif-bell" title="Notifications">🔔<span id="notif-badge">0</span></button>
          <div id="notif-dropdown"><p class="notif-empty">Loading…</p></div>
        </div>
        <div class="auth-links">
          <?php if ($user['role'] === 'admin'): ?>
          <a href="/newssite/admin/" class="btn-admin">Admin</a>
          <?php endif; ?>
          <a href="/newssite/logout.php">Sign Out</a>
        </div>
      <?php else: ?>
        <div class="auth-links">
          <a href="/newssite/login.php">Login</a>
          <a href="/newssite/register.php">Register</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>

<nav class="main-nav">
  <div class="container">
    <ul class="nav-list">
      <li><a href="/newssite/" class="<?= !$categorySlug ? 'active' : '' ?>">Home</a></li>
      <?php foreach ($categories as $cat): ?>
      <li>
        <a href="?category=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
          <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>

<?php if (!empty($articles) || $hero): ?>
<div class="breaking-bar">
  <div class="container">
    <span class="breaking-label">Latest</span>
    <div class="breaking-scroll">
      <div class="breaking-inner" id="ticker">
        <?php
        $allForTicker = $articleModel->getAll(10, 0, null);
        foreach ($allForTicker as $t):
        ?>
        <a href="/newssite/article.php?id=<?= (int) $t['id'] ?>">
          <?= htmlspecialchars($t['headline'], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
        <?php foreach ($allForTicker as $t): ?>
        <a href="/newssite/article.php?id=<?= (int) $t['id'] ?>">
          <?= htmlspecialchars($t['headline'], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="page-wrap">
  <main>
    <?php if ($hero): ?>
    <article class="hero-story">
      <?php if ($hero['image_path']): ?>
        <img class="hero-story__img"
             src="<?= htmlspecialchars($hero['image_path'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($hero['headline'], ENT_QUOTES, 'UTF-8') ?>">
      <?php else: ?>
        <div class="hero-story__img-placeholder">📰</div>
      <?php endif; ?>
      <div class="hero-story__body">
        <span class="hero-story__cat"><?= htmlspecialchars($hero['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
        <h1 class="hero-story__headline">
          <a href="/newssite/article.php?id=<?= (int) $hero['id'] ?>">
            <?= htmlspecialchars($hero['headline'], ENT_QUOTES, 'UTF-8') ?>
          </a>
        </h1>
        <p class="hero-story__excerpt">
          <?= htmlspecialchars(mb_substr(strip_tags($hero['content']), 0, 200) . '…', ENT_QUOTES, 'UTF-8') ?>
        </p>
        <div class="hero-story__meta">
          <span>By <?= htmlspecialchars($hero['author'], ENT_QUOTES, 'UTF-8') ?></span>
          <span><?= date('d-m-Y | g:i A', strtotime($hero['published_at'])) ?></span>
        </div>
        <a href="/newssite/article.php?id=<?= (int) $hero['id'] ?>" class="read-more">Read More →</a>
      </div>
    </article>
    <?php endif; ?>

    <?php if (!empty($articles)): ?>
    <div class="section-label" style="margin-bottom:0;">Top Stories</div>
    <div class="article-grid">
      <?php foreach ($articles as $a): ?>
      <article class="article-card">
        <?php if ($a['image_path']): ?>
          <img class="article-card__img"
               src="<?= htmlspecialchars($a['image_path'], ENT_QUOTES, 'UTF-8') ?>"
               alt="<?= htmlspecialchars($a['headline'], ENT_QUOTES, 'UTF-8') ?>"
               loading="lazy">
        <?php else: ?>
          <div class="article-card__img-placeholder">📰</div>
        <?php endif; ?>
        <div class="article-card__body">
          <span class="article-card__cat"><?= htmlspecialchars($a['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
          <h2 class="article-card__headline">
            <a href="/newssite/article.php?id=<?= (int) $a['id'] ?>">
              <?= htmlspecialchars($a['headline'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </h2>
          <div class="article-card__meta">
            <span><?= htmlspecialchars($a['author'], ENT_QUOTES, 'UTF-8') ?></span>
            <span><?= date('d M Y', strtotime($a['published_at'])) ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php elseif (!$hero): ?>
    <div style="background:var(--white);border:1px solid var(--border);padding:3rem;text-align:center;color:var(--muted);">
      No articles published yet.
    </div>
    <?php endif; ?>
  </main>

  <aside class="sidebar">
    <div class="sidebar-widget">
      <div class="section-label">Latest News</div>
      <div class="latest-list">
        <?php
        $latest = $articleModel->getAll(8, 0, null);
        foreach ($latest as $l):
        ?>
        <div class="latest-item">
          <?php if ($l['image_path']): ?>
            <img class="latest-thumb"
                 src="<?= htmlspecialchars($l['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                 alt="" loading="lazy">
          <?php else: ?>
            <div class="latest-thumb-placeholder">📰</div>
          <?php endif; ?>
          <div class="latest-text">
            <a class="latest-headline" href="/newssite/article.php?id=<?= (int) $l['id'] ?>">
              <?= htmlspecialchars(mb_substr($l['headline'], 0, 75), ENT_QUOTES, 'UTF-8') ?>
            </a>
            <span class="latest-date"><?= date('d-m-Y | g:i A', strtotime($l['published_at'])) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="sidebar-widget" style="padding:1.25rem;">
      <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:1rem;">Categories</div>
      <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
        <?php foreach ($categories as $cat): ?>
        <a href="?category=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"
           style="background:<?= $categorySlug === $cat['slug'] ? 'var(--red)' : 'var(--bg)' ?>;color:<?= $categorySlug === $cat['slug'] ? '#fff' : 'var(--dark)' ?>;font-size:.72rem;font-weight:700;padding:.3rem .7rem;border:1px solid var(--border);display:inline-block;transition:all .15s;border-radius:2px;">
          <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>
</div>

<footer class="site-footer">
  <div class="container">
    <div class="footer-top">
      <div>
        <div class="footer-logo">Daily<span>Dispatch</span></div>
        <p>A news platform built with PHP 8.2, MySQL, and Vanilla JS for the NSBM Web Application Development module.</p>
      </div>
      <div class="footer-col">
        <h4>Sections</h4>
        <ul>
          <?php foreach ($categories as $cat): ?>
          <li><a href="?category=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Pages</h4>
        <ul>
          <li><a href="/newssite/">Home</a></li>
          <li><a href="/newssite/about.php">About Us</a></li>
          <li><a href="/newssite/contact.php">Contact</a></li>
          <li><a href="/newssite/register.php">Register</a></li>
          <li><a href="/newssite/login.php">Login</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <ul>
          <li><a href="mailto:news@dailydispatch.lk">news@dailydispatch.lk</a></li>
          <li><a href="/newssite/contact.php">Send a message</a></li>
          <li><a href="https://github.com" target="_blank">GitHub Repo</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      &copy; <?= date('Y') ?> DailyDispatch &mdash; Built with PHP 8.2, MySQL, Vanilla JS 
    </div>
  </div>
</footer>

<script src="/newssite/assets/js/notifications.js"></script>
<script src="/newssite/assets/js/main.js"></script>
</body>
</html>