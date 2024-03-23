<?php

require_once 'vendor/autoload.php'; // Assuming you installed the firebase/php-jwt library via Composer
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

define('JWT_SECRET_KEY', 'your_secret_key_here'); // Replace with a secure secret key

function generateJWT($userId, $role)
{
    $issuedAt = time();
    $expirationTime = $issuedAt + (60 * 60 * 24 * 30); // Token valid for 30 days
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'uid' => $userId,
        'role' => $role
    ];

    $jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');

    return $jwt;
}

function validateJWT($jwt)
{
    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
        return $decoded->payload;
    } catch (Exception $e) {
        return null;
    }
}