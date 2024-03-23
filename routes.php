<?php

require_once 'autoload.php';
require_once 'config/database.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/PostsController.php';
require_once 'utils/auth.php';

$authController = new AuthController($pdo);
$postsController = new PostsController($pdo);

$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Helper function to get the ID from the request URI
function getIdFromUri($uri)
{
    $parts = explode('/', $uri);
    return end($parts);
}

// Authentication routes
switch ($request_method) {
    case 'POST':
        if ($request_uri === '/api/register') {
            $authController->register();
        } elseif ($request_uri === '/api/login') {
            $authController->login();
        }
        break;
}

// User routes
switch ($request_method) {
    case 'GET':
        if ($request_uri === '/api/users') {
            authorizeRole('admin');
            $userController->getAllUsers();
        } elseif (preg_match('/^\/api\/users\/(\d+)$/', $request_uri, $matches)) {
            $userId = $matches[1];
            authorizeRole('admin');
            $userController->getUserById($userId);
        }
        break;
    case 'POST':
        if ($request_uri === '/api/users') {
            authorizeRole('admin');
            $userController->createUser();
        }
        break;
    case 'PUT':
        if (preg_match('/^\/api\/users\/(\d+)$/', $request_uri, $matches)) {
            $userId = $matches[1];
            authorizeRole('admin');
            $userController->updateUser($userId);
        }
        break;
    case 'DELETE':
        if (preg_match('/^\/api\/users\/(\d+)$/', $request_uri, $matches)) {
            $userId = $matches[1];
            authorizeRole('admin');
            $userController->deleteUser($userId);
        }
        break;
}

// Post routes
switch ($request_method) {
    case 'GET':
        if ($request_uri === '/api/posts') {
            $postsController->getAllPosts();
        } elseif (preg_match('/^\/api\/posts\/(\d+)$/', $request_uri, $matches)) {
            $postId = $matches[1];
            authenticateJWT($pdo);
            $postsController->getPostById($postId);
        }
        break;
    case 'POST':
        if ($request_uri === '/api/posts') {
            authorizeRole('author');
            $postsController->createPost();
        }
        break;
    case 'PUT':
        if (preg_match('/^\/api\/posts\/(\d+)$/', $request_uri, $matches)) {
            $postId = $matches[1];
            authorizeRole('author');
            $postsController->updatePost($postId);
        }
        break;
    case 'DELETE':
        if (preg_match('/^\/api\/posts\/(\d+)$/', $request_uri, $matches)) {
            $postId = $matches[1];
            authorizeRole('admin');
            $postsController->deletePost($postId);
        }
        break;
}

// Category routes
switch ($request_method) {
    case 'GET':
        if ($request_uri === '/api/categories') {
            $postsController->getAllCategories();
        } elseif (preg_match('/^\/api\/categories\/(\d+)$/', $request_uri, $matches)) {
            $categoryId = $matches[1];
            $postsController->getCategoryById($categoryId);
        }
        break;
    case 'POST':
        if ($request_uri === '/api/categories') {
            authorizeRole('admin');
            $postsController->createCategory();
        }
        break;
    case 'PUT':
        if (preg_match('/^\/api\/categories\/(\d+)$/', $request_uri, $matches)) {
            $categoryId = $matches[1];
            authorizeRole('admin');
            $postsController->updateCategory($categoryId);
        }
        break;
    case 'DELETE':
        if (preg_match('/^\/api\/categories\/(\d+)$/', $request_uri, $matches)) {
            $categoryId = $matches[1];
            authorizeRole('admin');
            $postsController->deleteCategory($categoryId);
        }
        break;
}

// Comment routes
switch ($request_method) {
    case 'GET':
        if ($request_uri === '/api/comments') {
            authenticateJWT($pdo);
            $postsController->getAllComments();
        } elseif (preg_match('/^\/api\/comments\/(\d+)$/', $request_uri, $matches)) {
            $commentId = $matches[1];
            authenticateJWT($pdo);
            $postsController->getCommentById($commentId);
        }
        break;
    case 'POST':
        if ($request_uri === '/api/comments') {
            authenticateJWT($pdo);
            $postsController->createComment();
        }
        break;
    case 'PUT':
        if (preg_match('/^\/api\/comments\/(\d+)$/', $request_uri, $matches)) {
            $commentId = $matches[1];
            authorizeRole('admin');
            $postsController->updateComment($commentId);
        }
        break;
    case 'DELETE':
        if (preg_match('/^\/api\/comments\/(\d+)$/', $request_uri, $matches)) {
            $commentId = $matches[1];
            authorizeRole('admin');
            $postsController->deleteComment($commentId);
        }
        break;
}

// ... Additional routes for other features (tags, search, etc.)

// Handle invalid requests
http_response_code(404);
echo json_encode(['error' => 'Not Found']);