<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/User.php';
require_once __DIR__ . '/includes/Auth.php';

Auth::start();
if (Auth::check()) { header('Location: /newssite/'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::validateCsrf();
    if (Auth::login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
        header('Location: /newssite/');
        exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — DailyDispatch</title>
<link rel="stylesheet" href="/newssite/assets/css/main.css">
</head>
<body style="background:var(--bg);">
<div class="topbar">
  <div class="container">
    <span class="topbar-date"><?= date('l, F j, Y') ?></span>
  </div>
</div>
<header class="site-header">
  <div class="container">
    <a href="/newssite/" class="site-logo">Daily<span>Dispatch</span></a>
  </div>
</header>
<nav class="main-nav"><div class="container"><ul class="nav-list"><li><a href="/newssite/">Home</a></li></ul></div></nav>

<div class="auth-page-wrap">
  <div class="auth-box">
    <div class="auth-box-header">
      <h2>Sign In</h2>
    </div>
    <div class="auth-box-body">
      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required autofocus>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-primary">Sign In</button>
      </form>
      <p style="text-align:center;margin-top:1.25rem;font-size:.85rem;color:var(--muted);">
        No account? <a href="/newssite/register.php" style="color:var(--red);font-weight:700;">Register here</a>
      </p>
    </div>
  </div>
</div>

<footer class="site-footer"><div class="container"><div class="footer-bottom">&copy; <?= date('Y') ?> DailyDispatch</div></div></footer>
</body>
</html>