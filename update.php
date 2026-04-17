<?php
require "db.php";

function scrapePage($url)
{
    $html = file_get_contents($url);

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $nodes = $xpath->query("//div[contains(@class,'latest-movie-img-container')]");

    $data = [];

    foreach ($nodes as $node) {

        $titleNode = $xpath->query(".//div[@class='movie-title']//a", $node)->item(0);
        $imgNode   = $xpath->query(".//img", $node)->item(0);
        $linkNode  = $xpath->query(".//div[@class='movie-title']//a", $node)->item(0);
        $epNode    = $xpath->query(".//span[contains(@class,'label')]", $node)->item(0);

        $title = $titleNode?->nodeValue ? trim($titleNode->nodeValue) : null;
        $image = $imgNode?->getAttribute("src");
        $link  = $linkNode?->getAttribute("href");
        $ep    = $epNode?->nodeValue ? trim($epNode->nodeValue) : null;

        if ($title) {
            $data[] = [$title, $image, $link, $ep];
        }
    }

    return $data;
}

/*
    قاعدة الصفحات:
    page 1 = main page
    page 2 = 24.html
    page 3 = 48.html
*/

$allData = [];

// عدد الصفحات (غيره حسب الموقع)
$pages = 58;

for ($i = 0; $i < $pages; $i++) {

    if ($i == 0) {
        $url = "https://aj.alooytv13.xyz/tv-series.html";
    } else {
        $offset = $i * 24;
        $url = "https://aj.alooytv13.xyz/tvseries/home/{$offset}.html";
    }

    echo "Scraping: $url\n";

    $allData = array_merge($allData, scrapePage($url));

    sleep(1); // مهم عشان ما ينحظر
}

/*
    امسح القديم
*/
$pdo->exec("DELETE FROM movies");

/*
    إدخال جديد
*/
$stmt = $pdo->prepare("
    INSERT INTO movies (title, image, link, episodes)
    VALUES (?, ?, ?, ?)
");

foreach ($allData as $row) {
    $stmt->execute($row);
}

echo "Done: " . count($allData) . " records updated";
