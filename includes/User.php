<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function register(string $username, string $email, string $password): bool
    {
        
        if (mb_strlen($username) > MAX_USERNAME_LEN) {
            throw new \InvalidArgumentException('Username exceeds ' . MAX_USERNAME_LEN . ' characters.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        try {
            $this->db->run(
                'INSERT INTO users (username, email, password) VALUES (?, ?, ?)',
                [$username, $email, $hash]
            );
            return true;
        } catch (\PDOException $e) {
            
            if ($e->getCode() === '23000') {
                throw new \RuntimeException('Email already registered.');
            }
            throw $e;
        }
    }

    public function findByEmail(string $email): ?array
    {
        $row = $this->db->run(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        )->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $row = $this->db->run(
            'SELECT id, username, email, role, created_at FROM users WHERE id = ? LIMIT 1',
            [$id]
        )->fetch();
        return $row ?: null;
    }

    
    public function toggleFavoriteCategory(int $userId, int $categoryId): string
    {
        $exists = $this->db->run(
            'SELECT 1 FROM user_favorite_categories WHERE user_id = ? AND category_id = ?',
            [$userId, $categoryId]
        )->fetch();

        if ($exists) {
            $this->db->run(
                'DELETE FROM user_favorite_categories WHERE user_id = ? AND category_id = ?',
                [$userId, $categoryId]
            );
            return 'removed';
        } else {
            $this->db->run(
                'INSERT INTO user_favorite_categories (user_id, category_id) VALUES (?, ?)',
                [$userId, $categoryId]
            );
            return 'added';
        }
    }

    public function getFavoriteCategories(int $userId): array
    {
        return $this->db->run(
            'SELECT c.id, c.name, c.slug
             FROM user_favorite_categories ufc
             JOIN categories c ON c.id = ufc.category_id
             WHERE ufc.user_id = ?',
            [$userId]
        )->fetchAll();
    }
}