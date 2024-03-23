<?php

require_once 'utils/image.php';

class Post
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllPosts($filters = [])
    {
        $query = 'SELECT p.*, u.username AS author_name, c.name AS category_name
                FROM posts p
                JOIN users u ON p.author_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id';

        // Apply filters
        $params = [];
        if (!empty($filters['category_id'])) {
            $query .= ' WHERE p.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['author_id'])) {
            $query .= (empty($params) ? ' WHERE' : ' AND') . ' p.author_id = :author_id';
            $params['author_id'] = $filters['author_id'];
        }

        if (!empty($filters['search'])) {
            $query .= (empty($params) ? ' WHERE' : ' AND') . ' (p.title ILIKE :search OR p.content ILIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $query .= ' ORDER BY p.created_at DESC';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostById($id)
    {
        $stmt = $this->pdo->prepare('SELECT p.*, u.username AS author_name, c.name AS category_name
                                    FROM posts p
                                    JOIN users u ON p.author_id = u.id
                                    LEFT JOIN categories c ON p.category_id = c.id
                                    WHERE p.id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createPost($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO posts (title, content, author_id, category_id, is_draft)
                                    VALUES (:title, :content, :author_id, :category_id, :is_draft)
                                    RETURNING id');
        $stmt->execute([
            'title' => $data['title'],
            'content' => $data['content'],
            'author_id' => $data['author_id'],
            'category_id' => $data['category_id'] ?? null,
            'is_draft' => $data['is_draft'] ?? false,
        ]);
        $postId = $stmt->fetchColumn();

        if (!empty($data['tags'])) {
            $this->assignTagsToPosts($postId, $data['tags']);
        }

        if (!empty($data['image'])) {
            $imageUrl = uploadImage($data['image']);
            $this->updatePostImage($postId, $imageUrl);
        }

        return $postId;
    }

    public function updatePost($id, $data)
    {
        $stmt = $this->pdo->prepare('UPDATE posts
                                    SET title = :title,
                                        content = :content,
                                        category_id = :category_id,
                                        is_draft = :is_draft
                                    WHERE id = :id');
        $stmt->execute([
            'title' => $data['title'],
            'content' => $data['content'],
            'category_id' => $data['category_id'] ?? null,
            'is_draft' => $data['is_draft'] ?? false,
            'id' => $id,
        ]);

        if (!empty($data['tags'])) {
            $this->assignTagsToPosts($id, $data['tags']);
        }

        if (!empty($data['image'])) {
            $imageUrl = uploadImage($data['image']);
            $this->updatePostImage($id, $imageUrl);
        }

        return $stmt->rowCount();
    }

    public function deletePost($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }

    public function togglePostPublishStatus($id)
    {
        $post = $this->getPostById($id);
        $newDraftStatus = !$post['is_draft'];

        $stmt = $this->pdo->prepare('UPDATE posts SET is_draft = :is_draft WHERE id = :id');
        $stmt->execute(['is_draft' => $newDraftStatus, 'id' => $id]);
        return $stmt->rowCount();
    }

    private function assignTagsToPosts($postId, $tags)
    {
        $stmt = $this->pdo->prepare('DELETE FROM post_tags WHERE post_id = :post_id');
        $stmt->execute(['post_id' => $postId]);

        foreach ($tags as $tagId) {
            $stmt = $this->pdo->prepare('INSERT INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)');
            $stmt->execute(['post_id' => $postId, 'tag_id' => $tagId]);
        }
    }

    private function updatePostImage($postId, $imageUrl)
    {
        $stmt = $this->pdo->prepare('UPDATE posts SET image_url = :image_url WHERE id = :id');
        $stmt->execute(['image_url' => $imageUrl, 'id' => $postId]);
    }
}