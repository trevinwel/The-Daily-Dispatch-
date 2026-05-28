<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

class Article
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    
    public function getAll(int $limit = 12, int $offset = 0, ?int $categoryId = null): array
    {
        if ($categoryId !== null) {
            $sql = 'SELECT a.*, c.name AS category_name, u.username AS author
                    FROM articles a
                    JOIN categories c ON c.id = a.category_id
                    JOIN users u ON u.id = a.author_id
                    WHERE a.category_id = :cat
                    ORDER BY a.published_at DESC
                    LIMIT :lim OFFSET :off';
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':cat', $categoryId, PDO::PARAM_INT);
        } else {
            $sql = 'SELECT a.*, c.name AS category_name, u.username AS author
                    FROM articles a
                    JOIN categories c ON c.id = a.category_id
                    JOIN users u ON u.id = a.author_id
                    ORDER BY a.published_at DESC
                    LIMIT :lim OFFSET :off';
            $stmt = $this->db->getConnection()->prepare($sql);
        }

        $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    
    public function search(string $query, int $limit = 12): array
    {
        $sql = 'SELECT a.*, c.name AS category_name, u.username AS author
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN users u ON u.id = a.author_id
                WHERE MATCH(a.headline, a.content) AGAINST (:q IN BOOLEAN MODE)
                ORDER BY a.published_at DESC
                LIMIT :lim';
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':q',   $query . '*', PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit,       PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    
    public function getById(int $id): ?array
    {
        $row = $this->db->run(
            'SELECT a.*, c.name AS category_name, u.username AS author
             FROM articles a
             JOIN categories c ON c.id = a.category_id
             JOIN users u ON u.id = a.author_id
             WHERE a.id = ?',
            [$id]
        )->fetch();

        return $row ?: null;
    }

   
    public function create(int $categoryId, int $authorId, string $headline, string $content, ?string $imagePath): int
    {
        
        if (mb_strlen($headline) > MAX_HEADLINE_LEN) {
            throw new \InvalidArgumentException('Headline exceeds ' . MAX_HEADLINE_LEN . ' characters.');
        }
        if (mb_strlen($content) > MAX_CONTENT_LEN) {
            throw new \InvalidArgumentException('Content exceeds ' . MAX_CONTENT_LEN . ' characters.');
        }

        $this->db->run(
            'INSERT INTO articles (category_id, author_id, headline, content, image_path)
             VALUES (?, ?, ?, ?, ?)',
            [$categoryId, $authorId, $headline, $content, $imagePath]
        );

        return (int) $this->db->getConnection()->lastInsertId();
    }

    public function getCategories(): array
    {
        return $this->db->run('SELECT * FROM categories ORDER BY name')->fetchAll();
    }

    public function delete(int $id): bool
    {
    
        $stmt = $this->db->run(
        'DELETE FROM articles WHERE id = ?',
        [$id]
    );
        return $stmt->rowCount() > 0;
    }
}




