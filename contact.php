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
$user     = Auth::user();
$today    = date('l, F j, Y');
$success  = '';
$error    = '';

$articleModel = new Article();
$categories   = $articleModel->getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::validateCsrf();

    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (mb_strlen($message) > 2000) {
        $error = 'Message cannot exceed 2000 characters.';
    } else {
        try {
            Database::getInstance()->run(
                'INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)',
                [mb_substr($name, 0, 100), mb_substr($email, 0, 255), mb_substr($subject, 0, 150), mb_substr($message, 0, 2000)]
            );
            $success = 'Thank you! Your message has been received. We will get back to you shortly.';
        } catch (\Exception $e) {
            $error = 'Could not send your message. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us — DailyDispatch</title>
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
      <li><a href="/newssite/about.php">About</a></li>
      <li><a href="/newssite/contact.php" class="active">Contact</a></li>
    </ul>
  </div>
</nav>

<div class="inner-page-hero">
  <h1>Contact Us</h1>
  <p>Have a story tip, feedback, or question? We want to hear from you.</p>
</div>

<div class="inner-page-content">
  <div class="contact-page-grid">
    <div class="info-panel">
      <h3>Get In Touch</h3>
      <div class="info-item">
        <span class="ico">📍</span>
        <div>
          <strong>Address</strong>
          <span>Wattala<br>Colombo<br>Sri Lanka</span>
        </div>
      </div>
      <div class="info-item">
        <span class="ico">📧</span>
        <div>
          <strong>Email</strong>
          <span>news@dailydispatch.lk</span>
        </div>
      </div>
      <div class="info-item">
        <span class="ico">📞</span>
        <div>
          <strong>Phone</strong>
          <span>+94 67 690 8070</span>
        </div>
      </div>
      <div class="info-item">
        <span class="ico">🕐</span>
        <div>
          <strong>Office Hours</strong>
          <span>Mon – Fri: 9:00 AM – 6:00 PM<br>Sat: 9:00 AM – 1:00 PM</span>
        </div>
      </div>
      <div class="info-item">
        <span class="ico">💡</span>
        <div>
          <strong>Story Tips</strong>
          <span>tips@dailydispatch.lk</span>
        </div>
      </div>
    </div>

    <div class="contact-form-box">
      <h3>Send Us a Message</h3>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
      <?php elseif ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <?php if (!$success): ?>
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <div class="form-row">
          <div class="form-group">
            <label for="name">Your Name *</label>
            <input type="text" id="name" name="name" required maxlength="100"
                   value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="subject">Subject *</label>
          <input type="text" id="subject" name="subject" required maxlength="150"
                 value="<?= htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
          <label for="message">Message *</label>
          <textarea id="message" name="message" rows="6" required maxlength="2000"><?= htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          <div class="counter" id="msg-counter">0 / 2000</div>
        </div>
        <button type="submit" class="btn-primary" style="width:auto;padding:.65rem 2rem;">Send Message →</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      &copy; <?= date('Y') ?> DailyDispatch &middot;
      <a href="/newssite/">Home</a> &middot;
      <a href="/newssite/about.php">About</a> &middot;
      <a href="/newssite/contact.php">Contact</a>
    </div>
  </div>
</footer>

<script src="/newssite/assets/js/notifications.js"></script>
<script>
(function() {
  const ta = document.getElementById('message');
  const c  = document.getElementById('msg-counter');
  if (!ta || !c) return;
  function upd() {
    const l = ta.value.length;
    c.textContent = l + ' / 2000';
    c.className = 'counter' + (l > 1800 ? ' counter-warn' : '') + (l >= 2000 ? ' counter-over' : '');
  }
  ta.addEventListener('input', upd);
  upd();
})();
</script>
</body>
</html>