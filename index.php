<?php
require 'db.php';

$stmt = $pdo->query("SELECT * FROM movies ORDER BY id ASC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
