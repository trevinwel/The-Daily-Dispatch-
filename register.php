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

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::validateCsrf();
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $confirm  =      $_POST['confirm']  ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        try {
            (new User())->register($username, $email, $password);
            $success = 'Account created successfully! <a href="/newssite/login.php" style="color:var(--red);font-weight:700;">Log in now</a>';
        } catch (\Exception $e) {
            $error = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — DailyDispatch</title>
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
<nav class="main-nav"><div class="container"><ul class="nav-list"><li><a href="/newssite/">Home</a></li><li><a href="/newssite/login.php">Login</a></li></ul></div></nav>

<div class="auth-page-wrap">
  <div class="auth-box">
    <div class="auth-box-header">
      <h2>Create Account</h2>
    </div>
    <div class="auth-box-body">
      <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

      <?php if (!$success): ?>
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <div class="form-group">
          <label for="username">Username (max 20 chars)</label>
          <input type="text" id="username" name="username" maxlength="20" required
                 value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div class="counter" id="username-counter">0 / 20</div>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
          <label for="password">Password (min 8 chars)</label>
          <input type="password" id="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
          <label for="confirm">Confirm Password</label>
          <input type="password" id="confirm" name="confirm" required>
        </div>
        <button type="submit" class="btn-primary">Create Account</button>
      </form>
      <p style="text-align:center;margin-top:1.25rem;font-size:.85rem;color:var(--muted);">
        Already have an account? <a href="/newssite/login.php" style="color:var(--red);font-weight:700;">Sign in</a>
      </p>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer class="site-footer"><div class="container"><div class="footer-bottom">&copy; <?= date('Y') ?> DailyDispatch</div></div></footer>
<script src="/newssite/assets/js/validation.js"></script>
</body>
</html>