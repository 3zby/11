<?php
include 'db.php';

// 🔹 سحب البيانات
function scrapeData() {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://aj.alooytv13.xyz/tv-series.html");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($ch);
    curl_close($ch);

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $items = [];

    $nodes = $xpath->query("//div[contains(@class,'movie-img')]");

    foreach ($nodes as $node) {

        $img = $xpath->query(".//img", $node)->item(0);
        $a = $xpath->query(".//a", $node)->item(0);
        $episodes = $xpath->query(".//span", $node)->item(0);

        if (!$img || !$a) continue;

        $items[] = [
            "title" => trim($img->getAttribute("alt")),
            "image" => $img->getAttribute("src"),
            "url" => $a->getAttribute("href"),
            "episodes" => $episodes ? trim($episodes->nodeValue) : ""
        ];
    }

    return $items;
}

// 🔹 هل نحدث؟
$lastUpdate = $pdo->query("SELECT created_at FROM series ORDER BY created_at DESC LIMIT 1")->fetchColumn();

$needUpdate = true;

if ($lastUpdate) {
    if (time() - strtotime($lastUpdate) < 300) {
        $needUpdate = false;
    }
}

// 🔥 تحديث البيانات
if ($needUpdate) {

    $data = scrapeData();

    foreach ($data as $item) {

        $stmt = $pdo->prepare("
            INSERT INTO series (title, image, url, episodes)
            VALUES (:title, :image, :url, :episodes)
            ON CONFLICT (url) DO UPDATE SET
                title = EXCLUDED.title,
                image = EXCLUDED.image,
                episodes = EXCLUDED.episodes,
                created_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute($item);
    }
}

// 🔹 عرض من القاعدة
$stmt = $pdo->query("SELECT * FROM series ORDER BY id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
