<?php
require "db.php";

$stmt = $pdo->query("SELECT * FROM movies ORDER BY id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
