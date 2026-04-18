<?php
require "db.php";

function get($url) {
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0\r\n"
        ]
    ];
    return file_get_contents($url, false, stream_context_create($opts));
}

$json = get("https://raw.githubusercontent.com/alooytv/link/refs/heads/main/data.json");
$data = json_decode($json, true);

foreach ($data as $item) {

    // ✅ فلترة
    if ($item['biolink_block_id'] != "3") continue;

    $html = get($item['location_url']);

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // أدق
    $movies = $xpath->query("//div[contains(@class,'movie-container')]//div[contains(@class,'col-md-2')]");

    foreach ($movies as $movie) {

        $titleNode = $xpath->query(".//h3/a", $movie)->item(0);
        $imgNode   = $xpath->query(".//img", $movie)->item(0);
        $linkNode  = $xpath->query(".//a[contains(@class,'ico-play')]", $movie)->item(0);
        $epNode    = $xpath->query(".//span[contains(@class,'label')]", $movie)->item(0);

        $title = trim($titleNode?->nodeValue ?? '');
        $url   = $linkNode?->getAttribute("href") ?? '';

        // ✅ معالجة lazy image
        $image = $imgNode?->getAttribute("src");
        if (!$image || str_contains($image, "blank_thumbnail")) {
            $image = $imgNode?->getAttribute("data-src");
        }

        $episodes = trim($epNode?->nodeValue ?? '');

        if (!$url) continue;

        // ---------------------------
        // ✅ جلب التفاصيل
        // ---------------------------
        $description = '';
        $release = '';
        $genres = [];

        try {
            $innerHtml = get($url);

            $dom2 = new DOMDocument();
            $dom2->loadHTML($innerHtml);
            $xp2 = new DOMXPath($dom2);

            // الوصف
            $descNode = $xp2->query("//div[contains(@class,'col-md-12')]//p")->item(0);
            $description = trim($descNode?->nodeValue ?? '');

            // Release
            foreach ($xp2->query("//p") as $p) {
                if (str_contains($p->nodeValue, "Release")) {
                    $release = trim(str_replace("Release:", "", $p->nodeValue));
                }
            }

            // Genres
            foreach ($xp2->query("//a[contains(@href,'genre')]") as $g) {
                $genres[] = trim($g->nodeValue);
            }

        } catch (Exception $e) {}

        // ---------------------------
        // ✅ تخزين
        // ---------------------------
        $stmt = $pdo->prepare("
            INSERT INTO movies 
            (title, image, url, episodes, biolink_block_id)
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

        echo "✔ $title\n";
    }
}

echo "DONE\n";
