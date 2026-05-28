<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/User.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Article.php';

Auth::requireAdmin();

$articleModel  = new Article();
$db            = Database::getInstance();
$user          = Auth::user();
$totalArticles = (int) $db->run('SELECT COUNT(*) FROM articles')->fetchColumn();
$totalUsers    = (int) $db->run('SELECT COUNT(*) FROM users')->fetchColumn();
$totalNotifs   = (int) $db->run('SELECT COUNT(*) FROM notifications WHERE is_read = 0')->fetchColumn();

$recentArticles = $db->run(
    'SELECT a.id, a.headline, c.name AS cat, a.published_at
     FROM articles a JOIN categories c ON c.id = a.category_id
     ORDER BY a.published_at DESC LIMIT 15'
)->fetchAll();

$successMsg = $_GET['success'] ?? '';
$errorMsg   = $_GET['error']   ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Admin</title>
<link rel="stylesheet" href="/newssite/assets/css/admin.css">
</head>
<body>

<aside class="admin-sidebar">
  <div class="sidebar-brand">Daily<span>Dispatch</span></div>
  <ul class="sidebar-nav">
    <li><a href="/newssite/admin/" class="active"><span class="ico">📊</span> Dashboard</a></li>
    <li><a href="/newssite/admin/dashboard.php"><span class="ico">✏️</span> New Article</a></li>
    <li><a href="/newssite/" target="_blank"><span class="ico">🌐</span> View Site</a></li>
    <li><a href="/newssite/logout.php"><span class="ico">🚪</span> Logout</a></li>
  </ul>
</aside>

<main class="admin-main">
  <div class="admin-topbar">
    <h1>Dashboard</h1>
    <span class="admin-topbar-meta">Welcome, <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?> &mdash; <?= date('d M Y') ?></span>
  </div>

  <?php if ($successMsg === 'deleted'): ?>
    <div class="alert alert-success">Article deleted successfully.</div>
  <?php elseif ($errorMsg): ?>
    <div class="alert alert-error">Something went wrong. Please try again.</div>
  <?php endif; ?>

  <div class="stat-row">
    <div class="stat-card">
      <span class="num"><?= $totalArticles ?></span>
      <span class="lbl">Total Articles</span>
    </div>
    <div class="stat-card">
      <span class="num"><?= $totalUsers ?></span>
      <span class="lbl">Registered Users</span>
    </div>
    <div class="stat-card">
      <span class="num"><?= $totalNotifs ?></span>
      <span class="lbl">Unread Notifications</span>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Recent Articles</h2>
    </div>
    <div class="admin-card-body" style="padding:0;">
      <table class="articles-table">
        <thead>
          <tr>
            <th>Headline</th>
            <th>Category</th>
            <th>Published</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentArticles as $a): ?>
          <tr>
            <td><?= htmlspecialchars(mb_strimwidth($a['headline'], 0, 65, '…'), ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge-cat"><?= htmlspecialchars($a['cat'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td style="white-space:nowrap;"><?= date('d M Y', strtotime($a['published_at'])) ?></td>
            <td>
              <div class="actions">
                <a href="/newssite/article.php?id=<?= (int) $a['id'] ?>" target="_blank" class="btn-view">View</a>
                <a href="/newssite/admin/edit_article.php?id=<?= (int) $a['id'] ?>" class="btn-edit">Edit</a>
                <form method="POST" action="/newssite/admin/delete_article.php" style="display:inline;"
                      onsubmit="return confirm('Delete this article? This cannot be undone.')">
                  <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                  <input type="hidden" name="article_id" value="<?= (int) $a['id'] ?>">
                  <button type="submit" class="btn-delete">Delete</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recentArticles)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:2rem;">No articles yet. <a href="/newssite/admin/dashboard.php" style="color:var(--red);">Publish one now →</a></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="margin-top:1rem;">
    <a href="/newssite/admin/dashboard.php" class="submit-btn" style="display:inline-block;padding:.65rem 1.5rem;">+ Publish New Article</a>
  </div>
</main>
</body>
</html>