<?php

// Database configuration
$host = 'localhost';
$dbname = 'blog_api';
$user = 'postgres';
$password = 'secret';

// Create a new PDO instance
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

// Helper function to execute SQL statements
function executeQuery($pdo, $sql, $params = [])
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Helper function to fetch a single row
function fetchSingleRow($pdo, $sql, $params = [])
{
    $stmt = executeQuery($pdo, $sql, $params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper function to fetch all rows
function fetchAllRows($pdo, $sql, $params = [])
{
    $stmt = executeQuery($pdo, $sql, $params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper function to execute an INSERT query and return the last inserted ID
function insertRow($pdo, $table, $data)
{
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

// Helper function to execute an UPDATE query
function updateRow($pdo, $table, $data, $where)
{
    $setClause = [];
    foreach ($data as $key => $value) {
        $setClause[] = "$key = :$key";
    }
    $setClause = implode(", ", $setClause);

    $sql = "UPDATE $table SET $setClause WHERE $where";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($data, $where));
    return $stmt->rowCount();
}

// Helper function to execute a DELETE query
function deleteRow($pdo, $table, $where)
{
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($where);
    return $stmt->rowCount();
}