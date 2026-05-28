<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Notification.php';

Auth::start();
header('Content-Type: application/json');

$user = Auth::user();
if (!$user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$notifModel = new Notification();
$notifModel->markAllRead((int) $user['id']);
echo json_encode(['success' => true]);