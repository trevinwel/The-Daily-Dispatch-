<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/Article.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}


$query = preg_replace('/[+\-><\(\)~*"@]+/', ' ', $query);
$query = mb_substr($query, 0, 100);  

$articleModel = new Article();
$results      = $articleModel->search($query);

$safe = array_map(function ($a) {
    return [
        'id'            => (int) $a['id'],
        'headline'      => htmlspecialchars($a['headline'],      ENT_QUOTES, 'UTF-8'),
        'category_name' => htmlspecialchars($a['category_name'], ENT_QUOTES, 'UTF-8'),
        'author'        => htmlspecialchars($a['author'],        ENT_QUOTES, 'UTF-8'),
        'published_at'  => $a['published_at'],
        'image_path'    => $a['image_path'] ? htmlspecialchars($a['image_path'], ENT_QUOTES, 'UTF-8') : null,
    ];
}, $results);

echo json_encode(['results' => $safe]);