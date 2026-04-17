<?php
require "db.php";

function scrape()
{
    $html = file_get_contents("https://aj.alooytv13.xyz/tv-series.html");

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $nodes = $xpath->query("//div[contains(@class,'latest-movie-img-container')]");

    $data = [];

    foreach ($nodes as $node) {

        $title = trim($xpath->query(".//div[@class='movie-title']//a", $node)->item(0)?->nodeValue);
        $image = $xpath->query(".//img", $node)->item(0)?->getAttribute("src");
        $link  = $xpath->query(".//div[@class='movie-title']//a", $node)->item(0)?->getAttribute("href");
        $ep    = trim($xpath->query(".//span[contains(@class,'label')]", $node)->item(0)?->nodeValue);

        if ($title) {
            $data[] = [$title, $image, $link, $ep];
        }
    }

    return $data;
}

// حذف القديم (اختياري)
$pdo->exec("DELETE FROM movies");

$stmt = $pdo->prepare("INSERT INTO movies (title, image, link, episodes) VALUES (?, ?, ?, ?)");

foreach (scrape() as $row) {
    $stmt->execute($row);
}

echo "Updated successfully";
