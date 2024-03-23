<?php

require_once 'utils/jwt.php';
require_once 'models/User.php';

function authenticateJWT($pdo)
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    if ($authHeader) {
        $authHeaderParts = explode(' ', $authHeader);
        $token = $authHeaderParts[1] ?? null;

        if ($token) {
            $payload = validateJWT($token);

            if ($payload) {
                $user = new User($pdo);
                $userData = $user->getUserById($payload->uid);

                if ($userData && $userData['role'] === $payload->role) {
                    return [
                        'userId' => $userData['id'],
                        'role' => $userData['role']
                    ];
                }
            }
        }
    }

    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

function authorizeRole($role)
{
    $authData = authenticateJWT($GLOBALS['pdo']);

    if ($authData['role'] === $role || $authData['role'] === 'admin') {
        return true;
    }

    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}