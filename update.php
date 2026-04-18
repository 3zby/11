<?php
require "db.php";

$json = file_get_contents("https://raw.githubusercontent.com/alooytv/link/refs/heads/main/data.json");
$data = json_decode($json, true);

foreach ($data as $item) {
    $html = file_get_contents($item['location_url']);

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $movies = $xpath->query("//div[contains(@class,'movie-img')]");

    foreach ($movies as $movie) {

        $titleNode = $xpath->query(".//h3/a", $movie)->item(0);
        $imgNode = $xpath->query(".//img", $movie)->item(0);
        $linkNode = $xpath->query(".//a[contains(@class,'ico-play')]", $movie)->item(0);
        $epNode = $xpath->query(".//span", $movie)->item(0);

        $title = $titleNode?->nodeValue;
        $image = $imgNode?->getAttribute("src") ?: $imgNode?->getAttribute("data-src");
        $url = $linkNode?->getAttribute("href");
        $episodes = $epNode?->nodeValue;

        if (!$url) continue;

        // UPSERT (تحديث أو إدخال)
        $stmt = $pdo->prepare("
            INSERT INTO movies (title, image, url, episodes, biolink_block_id)
            VALUES (:title, :image, :url, :episodes, :bio)
            ON CONFLICT (url) DO UPDATE SET
                title = EXCLUDED.title,
                image = EXCLUDED.image,
                episodes = EXCLUDED.episodes,
                biolink_block_id = EXCLUDED.biolink_block_id,
                updated_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            ":title" => $title,
            ":image" => $image,
            ":url" => $url,
            ":episodes" => $episodes,
            ":bio" => $item['biolink_block_id']
        ]);
    }
}

echo "DONE";
