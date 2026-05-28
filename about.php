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
$user  = Auth::user();
$today = date('l, F j, Y');

$db            = Database::getInstance();
$totalArticles = (int) $db->run('SELECT COUNT(*) FROM articles')->fetchColumn();
$totalUsers    = (int) $db->run('SELECT COUNT(*) FROM users')->fetchColumn();
$totalCats     = (int) $db->run('SELECT COUNT(*) FROM categories')->fetchColumn();
$articleModel  = new Article();
$categories    = $articleModel->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us — DailyDispatch</title>
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
      <li><a href="/newssite/about.php" class="active">About</a></li>
      <li><a href="/newssite/contact.php">Contact</a></li>
    </ul>
  </div>
</nav>

<div class="inner-page-hero">
  <h1>About The Daily Dispatch</h1>
  <p>student-built news platform.</p>
</div>

<div class="inner-page-content">
  <div class="stats-strip">
    <div class="stat-block">
      <span class="num"><?= $totalArticles ?></span>
      <span class="lbl">Articles Published</span>
    </div>
    <div class="stat-block">
      <span class="num"><?= $totalUsers ?></span>
      <span class="lbl">Registered Readers</span>
    </div>
    <div class="stat-block">
      <span class="num"><?= $totalCats ?></span>
      <span class="lbl">News Categories</span>
    </div>
  </div>

  <div class="content-grid">
    <div>
      <div class="prose-box" style="margin-bottom:16px;">
        <h2>Our Mission</h2>
        <p>The Daily Dispatch was built to demonstrate what a modern, secure, fully-featured news platform looks like when built from scratch — no frameworks, just clean PHP 8.2 OOP, MySQL, Vanilla JavaScript, and CSS3.</p>
        <p>Every feature, from the admin panel to the real-time notification center, was designed with security as the top priority: SQL injection protection, XSS prevention, CSRF tokens, and hardened file upload validation on every layer.</p>
        
      </div>

      <div class="prose-box" style="margin-bottom:16px;">
        <h2>Core Values</h2>
        <div class="values-grid">
          <div class="value-item">
            <div class="icon">🔒</div>
            <h4>Security First</h4>
            <p>Every query uses prepared statements. Every output is escaped. No exceptions.</p>
          </div>
          <div class="value-item">
            <div class="icon">📰</div>
            <h4>Real Journalism</h4>
            <p>Stories across technology, politics, sports, science, business, and health.</p>
          </div>
          <div class="value-item">
            <div class="icon">⚡</div>
            <h4>Performance</h4>
            <p>No frameworks, no bloat. Pure PHP and Vanilla JS for a fast experience.</p>
          </div>
          <div class="value-item">
            <div class="icon">📱</div>
            <h4>Responsive</h4>
            <p>CSS Grid layout that works on every screen size and device.</p>
          </div>
        </div>
      </div>

      <div class="prose-box">
        <h2>Technology Stack</h2>
        
        <div class="tech-tags">
          <span class="tech-tag">PHP 8.2 OOP</span>
          <span class="tech-tag">MySQL 8.0</span>
          <span class="tech-tag">PDO Prepared Statements</span>
          <span class="tech-tag red">Vanilla JS ES6</span>
          <span class="tech-tag red">CSS3 Grid</span>
          <span class="tech-tag">bcrypt Hashing</span>
          <span class="tech-tag">CSRF Protection</span>
          <span class="tech-tag">AJAX fetch() API</span>
          <span class="tech-tag red">Apache XAMPP</span>
          <span class="tech-tag">FULLTEXT Search</span>
          <span class="tech-tag red">Git / GitHub</span>
        </div>
      </div>
    </div>

    <div>
      <div class="prose-box">
        <h2>The Team</h2>
        <p style="margin-bottom:1rem;"></p>
        <div class="team-grid">
          <div class="team-card">
            <div class="team-avatar">T</div>
            <h4>Trevin</h4>
            <span>Team Lead</span>
          </div>
          <div class="team-card">
            <div class="team-avatar">T</div>
            <h4>Trevin</h4>
            <span>Frontend</span>
          </div>
          <div class="team-card">
            <div class="team-avatar">T</div>
            <h4>Trevin</h4>
            <span>Backend</span>
          </div>
          <div class="team-card">
            <div class="team-avatar">T</div>
            <h4>Trevin</h4>
            <span>Testing</span>
          </div>
        </div>         
        </p>
      </div>
    </div>
  </div>
</div>

<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      &copy; <?= date('Y') ?> DailyDispatch &mdash;  &mdash;
      <a href="/newssite/">Home</a> &middot;
      <a href="/newssite/about.php">About</a> &middot;
      <a href="/newssite/contact.php">Contact</a>
    </div>
  </div>
</footer>

<script src="/newssite/assets/js/notifications.js"></script>
</body>
</html>