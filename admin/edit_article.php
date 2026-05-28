<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/User.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Article.php';

Auth::requireAdmin();

$articleModel = new Article();
$categories   = $articleModel->getCategories();

$id      = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$article = $id ? $articleModel->getById((int) $id) : null;

if (!$article) {
    header('Location: /newssite/admin/');
    exit;
}

$success = '';
$error   = '';

// ── Handle update submission ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::validateCsrf();

    $headline   = trim($_POST['headline']    ?? '');
    $content    = trim($_POST['content']     ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $imagePath  = $article['image_path']; // keep existing by default

    $errors = [];

    if ($headline === '' || mb_strlen($headline) > MAX_HEADLINE_LEN) {
        $errors[] = 'Headline is required and must be under ' . MAX_HEADLINE_LEN . ' chars.';
    }
    if ($content === '' || mb_strlen($content) > MAX_CONTENT_LEN) {
        $errors[] = 'Content is required and must be under ' . MAX_CONTENT_LEN . ' chars.';
    }
    if ($categoryId <= 0) {
        $errors[] = 'Please select a category.';
    }

    // Handle new image upload if provided
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];

        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'Image exceeds 2MB limit.';
        } else {
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($ext, ALLOWED_EXT, true) || !in_array($mimeType, ALLOWED_MIME, true)) {
                $errors[] = 'Only JPG, PNG, WEBP images allowed.';
            } else {
                $newFilename = bin2hex(random_bytes(16)) . '.' . $ext;
                $destination = UPLOAD_DIR . $newFilename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Delete old image
                    if ($article['image_path']) {
                        $old = __DIR__ . '/../' . ltrim($article['image_path'], '/newssite/');
                        if (file_exists($old)) unlink($old);
                    }
                    $imagePath = UPLOAD_URL . $newFilename;
                }
            }
        }
    }

    if (empty($errors)) {
        Database::getInstance()->run(
            'UPDATE articles SET headline=?, content=?, category_id=?, image_path=? WHERE id=?',
            [$headline, $content, $categoryId, $imagePath, (int) $id]
        );
        $success = 'Article updated successfully!';
        // Refresh article data
        $article = $articleModel->getById((int) $id);
    } else {
        $error = implode(' | ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Article — Admin</title>
  <link rel="stylesheet" href="/newssite/assets/css/admin.css">
</head>
<body>

<aside class="admin-sidebar">
  <div class="sidebar__logo">The Daily <span>Admin</span></div>
  <ul class="sidebar__nav">
    <li><a href="/newssite/admin/">📊 Dashboard</a></li>
    <li><a href="/newssite/admin/dashboard.php">✏️ New Article</a></li>
    <li><a href="/newssite/" target="_blank">🌐 View Site</a></li>
    <li><a href="/newssite/logout.php">🚪 Logout</a></li>
  </ul>
</aside>

<main class="admin-main">
  <h1 class="page-title">Edit Article</h1>

  <?php if ($success): ?>
    <div style="background:#d4edda; color:#155724; padding:.85rem 1rem; border-radius:4px; margin-bottom:1rem;">
      ✓ <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="background:#f8d7da; color:#721c24; padding:.85rem 1rem; border-radius:4px; margin-bottom:1rem;">
      ✗ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div class="admin-card">
    <form method="POST" enctype="multipart/form-data" class="admin-form">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

      <div class="form-group">
        <label for="headline">Headline *</label>
        <input type="text" id="headline" name="headline"
               maxlength="100" required
               value="<?= htmlspecialchars($article['headline'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="counter" id="headline-counter">0 / 100</div>
      </div>

      <div class="form-group">
        <label for="category_id">Category *</label>
        <select id="category_id" name="category_id" required>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>"
              <?= (int)$cat['id'] === (int)$article['category_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="content">Content *</label>
        <textarea id="content" name="content" rows="12" required><?= htmlspecialchars($article['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
        <div class="counter" id="content-counter">0 / 10,000</div>
      </div>

      <div class="form-group">
        <?php if ($article['image_path']): ?>
          <label>Current Image</label>
          <img src="<?= htmlspecialchars($article['image_path'], ENT_QUOTES, 'UTF-8') ?>"
               style="max-width:200px; border-radius:4px; margin-bottom:.75rem; display:block;">
        <?php endif; ?>
        <label for="image">Replace Image (optional — JPG, PNG, WEBP, max 2MB)</label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
      </div>

      <div style="display:flex; gap:1rem; align-items:center;">
        <button type="submit" class="btn-submit">💾 Save Changes</button>
        <a href="/newssite/admin/" style="color:#6c757d; font-size:.875rem;">← Back to Dashboard</a>
      </div>
    </form>
  </div>
</main>

<script src="/newssite/assets/js/validation.js"></script>
</body>
</html>