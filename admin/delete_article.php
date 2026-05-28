<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/User.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Article.php';

Auth::requireAdmin();

// Only allow POST to prevent accidental deletion via URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /newssite/admin/');
    exit;
}

Auth::validateCsrf();

$id = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: /newssite/admin/?error=invalid');
    exit;
}

$articleModel = new Article();

// Get article first so we can delete the image file too
$article = $articleModel->getById((int) $id);

if ($article && $article['image_path']) {
    // Build the real file path from the URL path
    $filePath = __DIR__ . '/../' . ltrim($article['image_path'], '/newssite/');
    if (file_exists($filePath)) {
        unlink($filePath); // Delete image from disk too
    }
}

$deleted = $articleModel->delete((int) $id);

if ($deleted) {
    header('Location: /newssite/admin/?success=deleted');
} else {
    header('Location: /newssite/admin/?error=notfound');
}
exit;