<?php
require "db.php";

function scrapePage($url)
{
    $context = stream_context_create([
        "http" => [
            "timeout" => 15,
            "header" => "User-Agent: Mozilla/5.0"
        ]
    ]);

    $html = file_get_contents($url, false, $context);

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

        if ($title && $link) {
            $data[] = [$title, $image, $link, $ep];
        }
    }

    return $data;
}

/*
    عدد الصفحات
*/
$pages = 58;

$inserted = 0;

/*
    UPSERT (بدون حذف)
*/
$stmt = $pdo->prepare("
INSERT INTO movies (title, image, link, episodes)
VALUES (?, ?, ?, ?)
ON CONFLICT (link)
DO UPDATE SET
    title = EXCLUDED.title,
    image = EXCLUDED.image,
    episodes = EXCLUDED.episodes
");

for ($i = 0; $i < $pages; $i++) {

    if ($i == 0) {
        $url = "https://aj.alooytv13.xyz/tv-series.html";
    } else {
        $offset = $i * 24;
        $url = "https://aj.alooytv13.xyz/tvseries/home/{$offset}.html";
    }

    echo "Scraping: $url\n";

    $rows = scrapePage($url);

    foreach ($rows as $row) {
        $stmt->execute($row);
        $inserted++;
    }
}

/*
    النتيجة
*/
echo "Done. Processed: $inserted records\n";
