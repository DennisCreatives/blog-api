<?php

require_once 'models/User.php';
require_once 'utils/auth.php';

class UserController
{
    private $user;

    public function __construct($pdo)
    {
        $this->user = new User($pdo);
    }

    public function getAllUsers()
    {
        authorizeRole('admin');
        $users = $this->user->getAllUsers();
        header('Content-Type: application/json');
        echo json_encode($users);
    }

    public function getUserById($id)
    {
        authorizeRole('admin');
        $user = $this->user->getUserById($id);
        if ($user) {
            header('Content-Type: application/json');
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
    }

    public function createUser()
    {
        authorizeRole('admin');
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $this->user->createUser($data);
        if ($userId) {
            http_response_code(201);
            echo json_encode(['message' => 'User created successfully', 'id' => $userId]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to create user']);
        }
    }

    public function updateUser($id)
    {
        authorizeRole('admin');
        $data = json_decode(file_get_contents('php://input'), true);
        $rowsUpdated = $this->user->updateUser($id, $data);
        if ($rowsUpdated) {
            http_response_code(200);
            echo json_encode(['message' => 'User updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to update user']);
        }
    }

    public function deleteUser($id)
    {
        authorizeRole('admin');
        $rowsDeleted = $this->user->deleteUser($id);
        if ($rowsDeleted) {
            http_response_code(200);
            echo json_encode(['message' => 'User deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
    }
}