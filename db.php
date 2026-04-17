<?php

$host = getenv("DB_HOST");
$db   = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");

if (!$host || !$db || !$user || !$pass) {
    die("Missing DB env variables");
}

$dsn = "pgsql:host=$host;port=5432;dbname=$db;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
