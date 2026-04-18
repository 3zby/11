<?php
require "db.php";

// تقدر تضيف فلترة لاحقاً (مثلاً حسب biolink_block_id)
$biolink = $_GET['biolink_block_id'] ?? null;

if ($biolink) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE biolink_block_id = :bio ORDER BY id DESC");
    $stmt->execute([":bio" => $biolink]);
} else {
    $stmt = $pdo->query("SELECT * FROM movies ORDER BY id DESC");
}

$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($movies, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
