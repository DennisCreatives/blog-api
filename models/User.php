<?php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function register($username, $email, $password, $role = 'regular')
    {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL statement
        $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)');
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);

        // Execute the statement
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function login($usernameOrEmail, $password)
    {
        // Prepare the SQL statement
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :usernameOrEmail OR email = :usernameOrEmail');
        $stmt->bindParam(':usernameOrEmail', $usernameOrEmail);

        // Execute the statement
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            return false;
        }
    }

    public function getUserById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($id, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $fieldsString = implode(', ', $fields);
        $params[':id'] = $id;

        $stmt = $this->pdo->prepare("UPDATE users SET $fieldsString WHERE id = :id");
        return $stmt->execute($params);
    }

    public function deleteUser($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getAllUsers()
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUser($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password, role)
                                    VALUES (:username, :email, :password, :role)
                                    RETURNING id');
        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
        ]);
        return $stmt->fetchColumn();
    }
}