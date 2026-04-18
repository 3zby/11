<?php

require "db.php";

header('Content-Type: application/json; charset=utf-8');
$stmt = $pdo->query("SELECT * FROM movies ORDER BY sort_order ASC");
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => "success",
    "count" => count($movies),
    "data" => $movies
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
