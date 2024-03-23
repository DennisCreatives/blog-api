<?php

require_once 'models/Post.php';
require_once 'utils/auth.php';

class PostsController
{
    private $post;

    public function __construct($pdo)
    {
        $this->post = new Post($pdo);
    }

    public function getAllPosts()
    {
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'author_id' => $_GET['author_id'] ?? null,
            'search' => $_GET['search'] ?? null,
        ];

        $posts = $this->post->getAllPosts($filters);
        header('Content-Type: application/json');
        echo json_encode($posts);
    }

    public function getPostById($id)
    {
        $post = $this->post->getPostById($id);
        if ($post) {
            header('Content-Type: application/json');
            echo json_encode($post);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Post not found']);
        }
    }

    public function createPost()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $authData = authenticateJWT($GLOBALS['pdo']);

        $data['author_id'] = $authData['userId'];

        $postId = $this->post->createPost($data);
        if ($postId) {
            http_response_code(201);
            echo json_encode(['message' => 'Post created successfully', 'id' => $postId]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to create post']);
        }
    }

    public function updatePost($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $authData = authenticateJWT($GLOBALS['pdo']);

        $post = $this->post->getPostById($id);
        if ($post && ($post['author_id'] === $authData['userId'] || $authData['role'] === 'admin')) {
            $rowsUpdated = $this->post->updatePost($id, $data);
            if ($rowsUpdated) {
                http_response_code(200);
                echo json_encode(['message' => 'Post updated successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Failed to update post']);
            }
        } else {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
        }
    }

    public function deletePost($id)
    {
        $authData = authenticateJWT($GLOBALS['pdo']);

        if ($authData['role'] === 'admin') {
            $rowsDeleted = $this->post->deletePost($id);
            if ($rowsDeleted) {
                http_response_code(200);
                echo json_encode(['message' => 'Post deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Post not found']);
            }
        } else {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
        }
    }

    public function togglePostPublishStatus($id)
    {
        $authData = authenticateJWT($GLOBALS['pdo']);
        $post = $this->post->getPostById($id);

        if ($post && ($post['author_id'] === $authData['userId'] || $authData['role'] === 'admin')) {
            $rowsUpdated = $this->post->togglePostPublishStatus($id);
            if ($rowsUpdated) {
                $newStatus = $post['is_draft'] ? 'published' : 'draft';
                http_response_code(200);
                echo json_encode(['message' => "Post $newStatus successfully"]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Failed to toggle post publish status']);
            }
        } else {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
        }
    }
}