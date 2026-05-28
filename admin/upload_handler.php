<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Article.php';
require_once __DIR__ . '/../includes/Notification.php';

Auth::requireAdmin();
Auth::validateCsrf();

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}


$headline   = trim($_POST['headline']   ?? '');
$content    = trim($_POST['content']    ?? '');
$categoryId = (int) ($_POST['category_id'] ?? 0);
$authorId   = (int) Auth::user()['id'];


$errors = [];

if ($headline === '') {
    $errors[] = 'Headline is required.';
} elseif (mb_strlen($headline) > MAX_HEADLINE_LEN) {
    $errors[] = 'Headline exceeds ' . MAX_HEADLINE_LEN . ' characters.';
}

if ($content === '') {
    $errors[] = 'Content is required.';
} elseif (mb_strlen($content) > MAX_CONTENT_LEN) {
    $errors[] = 'Content exceeds ' . MAX_CONTENT_LEN . ' characters.';
}

if ($categoryId <= 0) {
    $errors[] = 'Please select a valid category.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['errors' => $errors]);
    exit;
}


$imagePath = null;

if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];

    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File upload failed with error code ' . $file['error']]);
        exit;
    }

    
    if ($file['size'] > MAX_FILE_SIZE) {
        http_response_code(413);
        echo json_encode(['error' => 'Image exceeds 2MB limit.']);
        exit;
    }

   
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        http_response_code(415);
        echo json_encode(['error' => 'Only JPG, PNG, and WEBP images are allowed.']);
        exit;
    }

    
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_MIME, true)) {
        http_response_code(415);
        echo json_encode(['error' => 'File MIME type mismatch. Upload rejected.']);
        exit;
    }

    
    $newFilename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = UPLOAD_DIR . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not save the uploaded file.']);
        exit;
    }

    $imagePath = UPLOAD_URL . $newFilename;
}


try {
    $articleModel = new Article();
    $articleId    = $articleModel->create($categoryId, $authorId, $headline, $content, $imagePath);

    
    $notifModel = new Notification();
    $notifModel->dispatch($articleId, $categoryId, $headline);

    echo json_encode([
        'success'    => true,
        'article_id' => $articleId,
        'message'    => 'Article published successfully.',
    ]);

} catch (\InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['error' => $e->getMessage()]);
} catch (\Exception $e) {
    error_log('[UPLOAD ERROR] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An internal error occurred.']);
}