<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

class Notification
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    
    public function dispatch(int $articleId, int $categoryId, string $headline): void
    {
        
        $users = $this->db->run(
            'SELECT user_id FROM user_favorite_categories WHERE category_id = ?',
            [$categoryId]
        )->fetchAll();

        if (empty($users)) return;

        $message = 'New article: ' . mb_substr($headline, 0, 80);

        $stmt = $this->db->getConnection()->prepare(
            'INSERT INTO notifications (user_id, article_id, message) VALUES (?, ?, ?)'
        );

        foreach ($users as $user) {
            $stmt->execute([$user['user_id'], $articleId, $message]);
        }
    }

    
    public function getUnread(int $userId): array
    {
        return $this->db->run(
            'SELECT n.*, a.headline
             FROM notifications n
             JOIN articles a ON a.id = n.article_id
             WHERE n.user_id = ? AND n.is_read = 0
             ORDER BY n.created_at DESC
             LIMIT 20',
            [$userId]
        )->fetchAll();
    }

    
    public function markAllRead(int $userId): void
    {
        $this->db->run(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0',
            [$userId]
        );
    }

    public function countUnread(int $userId): int
    {
        return (int) $this->db->run(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0',
            [$userId]
        )->fetchColumn();
    }
}