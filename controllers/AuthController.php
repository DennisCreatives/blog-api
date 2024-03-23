<?php

require_once 'models/User.php';
require_once 'utils/jwt.php';

class AuthController
{
    private $user;

    public function __construct($pdo)
    {
        $this->user = new User($pdo);
    }

    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];
        $role = $data['role'] ?? 'regular';

        if ($this->user->register($username, $email, $password, $role)) {
            http_response_code(201);
            echo json_encode(['message' => 'User registered successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to register user']);
        }
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $usernameOrEmail = $data['usernameOrEmail'];
        $password = $data['password'];

        $user = $this->user->login($usernameOrEmail, $password);

        if ($user) {
            // Generate JWT token
            $token = generateJWT($user['id'], $user['role']);

            http_response_code(200);
            echo json_encode(['token' => $token]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }
}