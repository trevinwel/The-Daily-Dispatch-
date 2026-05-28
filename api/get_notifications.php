<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Notification.php';

Auth::start();
header('Content-Type: application/json');
header('Cache-Control: no-store');

$user = Auth::user();
if (!$user) {
    echo json_encode(['notifications' => [], 'count' => 0]);
    exit;
}

$notifModel    = new Notification();
$notifications = $notifModel->getUnread((int) $user['id']);
$count         = count($notifications);

// XSS-safe: escape all output
$safe = array_map(function ($n) {
    return [
        'id'         => (int) $n['id'],
        'message'    => htmlspecialchars($n['message'],    ENT_QUOTES, 'UTF-8'),
        'headline'   => htmlspecialchars($n['headline'],   ENT_QUOTES, 'UTF-8'),
        'article_id' => (int) $n['article_id'],
        'created_at' => $n['created_at'],
    ];
}, $notifications);

echo json_encode(['notifications' => $safe, 'count' => $count]);