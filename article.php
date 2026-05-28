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

$id           = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$articleModel = new Article();

if (!$id || !($article = $articleModel->getById((int) $id))) {
    http_response_code(404);
    die('<h1 style="font-family:sans-serif;text-align:center;margin-top:4rem;">404 — Article Not Found</h1>');
}

$categories = $articleModel->getCategories();
$related    = $articleModel->getAll(5, 0, (int) $article['category_id']);
$related    = array_filter($related, fn($r) => (int)$r['id'] !== (int)$article['id']);
$related    = array_slice(array_values($related), 0, 4);
$user       = Auth::user();
$today      = date('l, F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($article['headline'], ENT_QUOTES, 'UTF-8') ?> — DailyDispatch</title>
<link rel="stylesheet" href="/newssite/assets/css/main.css">
</head>
<body>

<div class="topbar">
  <div class="container">
    <span class="topbar-date"><?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?></span>
    <div class="topbar-links">
      <a href="/newssite/about.php">About</a>
      <a href="/newssite/contact.php">Contact</a>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="container">
    <a href="/newssite/" class="site-logo">Daily<span>Dispatch</span></a>
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
      <li><a href="/newssite/">Home</a></li>
      <?php foreach ($categories as $cat): ?>
      <li><a href="/newssite/?category=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
      </a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>

<div class="article-full-wrap">
  <main>
    <div class="article-full-body">
      <span class="article-full-cat"><?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
      <h1 class="article-full-headline"><?= htmlspecialchars($article['headline'], ENT_QUOTES, 'UTF-8') ?></h1>
      <div class="article-full-meta">
        <span>By <strong><?= htmlspecialchars($article['author'], ENT_QUOTES, 'UTF-8') ?></strong></span>
        <span><?= date('d-m-Y | g:i A', strtotime($article['published_at'])) ?></span>
      </div>
      <?php if ($article['image_path']): ?>
        <img class="article-full-image"
             src="<?= htmlspecialchars($article['image_path'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($article['headline'], ENT_QUOTES, 'UTF-8') ?>">
      <?php endif; ?>
      <div class="article-full-content">
        <?= nl2br(htmlspecialchars($article['content'], ENT_QUOTES, 'UTF-8')) ?>
      </div>
    </div>

    <?php if (!empty($related)): ?>
    <div class="section-label" style="margin-top:20px;">Related Stories</div>
    <div class="article-grid">
      <?php foreach ($related as $r): ?>
      <article class="article-card">
        <?php if ($r['image_path']): ?>
          <img class="article-card__img" src="<?= htmlspecialchars($r['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
        <?php else: ?>
          <div class="article-card__img-placeholder">📰</div>
        <?php endif; ?>
        <div class="article-card__body">
          <span class="article-card__cat"><?= htmlspecialchars($r['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
          <h2 class="article-card__headline">
            <a href="/newssite/article.php?id=<?= (int) $r['id'] ?>">
              <?= htmlspecialchars($r['headline'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </h2>
          <div class="article-card__meta">
            <span><?= htmlspecialchars($r['author'], ENT_QUOTES, 'UTF-8') ?></span>
            <span><?= date('d M Y', strtotime($r['published_at'])) ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>

  <aside class="sidebar">
    <div class="sidebar-widget">
      <div class="section-label">Latest News</div>
      <div class="latest-list">
        <?php
        $latest = $articleModel->getAll(8);
        foreach ($latest as $l):
        ?>
        <div class="latest-item">
          <?php if ($l['image_path']): ?>
            <img class="latest-thumb" src="<?= htmlspecialchars($l['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
          <?php else: ?>
            <div class="latest-thumb-placeholder">📰</div>
          <?php endif; ?>
          <div class="latest-text">
            <a class="latest-headline" href="/newssite/article.php?id=<?= (int) $l['id'] ?>">
              <?= htmlspecialchars(mb_substr($l['headline'], 0, 70), ENT_QUOTES, 'UTF-8') ?>
            </a>
            <span class="latest-date"><?= date('d-m-Y', strtotime($l['published_at'])) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>
</div>

<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      &copy; <?= date('Y') ?> DailyDispatch &mdash; 
      <a href="/newssite/">Home</a> &middot;
      <a href="/newssite/about.php">About</a> &middot;
      <a href="/newssite/contact.php">Contact</a>
    </div>
  </div>
</footer>

<script src="/newssite/assets/js/notifications.js"></script>
</body>
</html>