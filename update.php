<?php

require "db.php";

set_time_limit(0);
ignore_user_abort(true);

$jsonUrl = "https://raw.githubusercontent.com/alooytv/link/refs/heads/main/data.json";

/* =========================
   CURL FAST
========================= */
function curl_get($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 6,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => '',
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0'
        ],
    ]);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

/* =========================
   GET BASE URL
========================= */
$jsonData = curl_get($jsonUrl);
$data = json_decode($jsonData, true);

$startUrl = $data[0]['location_url'];

$parsed = parse_url($startUrl);
$baseUrl = $parsed['scheme'] . "://" . $parsed['host'] . "/";

/* =========================
   QUEUE SYSTEM (IMPORTANT)
========================= */
$step = 1; // صفحة واحدة فقط كل تشغيل (خفيف جدًا)

$stmtState = $pdo->prepare("SELECT value FROM scraper_state WHERE key = 'page'");
$stmtSet   = $pdo->prepare("INSERT INTO scraper_state (key,value)
                            VALUES ('page',:v)
                            ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value");

$page = $stmtState->execute() ? (int)$stmtState->fetchColumn() : 0;

if (!$page) $page = 0;

/* =========================
   BUILD PAGE URL
========================= */
$offset = $page * 24;

$url = ($page == 0)
    ? $startUrl
    : $baseUrl . "tvseries/home/{$offset}.html";

/* =========================
   FETCH PAGE
========================= */
$html = curl_get($url);

if (!$html) {
    die("No page found");
}

$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

libxml_use_internal_errors(true);

$dom = new DOMDocument();
@$dom->loadHTML($html);
$xp = new DOMXPath($dom);

$cards = $xp->query("//div[contains(@class,'latest-movie-img-container')]");

if (!$cards || $cards->length == 0) {
    die("No data in page $page");
}

/* =========================
   DB
========================= */
$stmt = $pdo->prepare("
INSERT INTO movies (title, url, image, episodes, release_date, genre, sort_order)
VALUES (:title, :url, :image, :episodes, :release_date, :genre, :sort_order)
ON CONFLICT (url) DO UPDATE SET
    title = EXCLUDED.title,
    image = EXCLUDED.image,
    episodes = EXCLUDED.episodes,
    sort_order = EXCLUDED.sort_order
");

$stmtCheck = $pdo->prepare("SELECT 1 FROM movies WHERE url = ?");

$index = $page * 24;

/* =========================
   PROCESS PAGE ONLY
========================= */
foreach ($cards as $card) {

    $titleNode = $xp->query(".//div[contains(@class,'movie-title')]//a", $card);
    $urlNode   = $xp->query(".//div[contains(@class,'movie-title')]//a", $card);
    $imgNode   = $xp->query(".//img", $card);
    $epNode    = $xp->query(".//div[contains(@class,'video_quality')]//span", $card);

    $title = $titleNode->length ? trim($titleNode[0]->nodeValue) : null;
    $url   = $urlNode->length ? trim($urlNode[0]->getAttribute("href")) : null;
    $image = $imgNode->length ? trim($imgNode[0]->getAttribute("src")) : null;
    $ep    = $epNode->length ? trim($epNode[0]->nodeValue) : null;

    if (!$title || !$url) continue;

    $stmtCheck->execute([$url]);
    if ($stmtCheck->fetchColumn()) continue;

    $index++;

    $stmt->execute([
        ":title" => $title,
        ":url" => $url,
        ":image" => $image,
        ":episodes" => $ep,
        ":release_date" => null,
        ":genre" => null,
        ":sort_order" => $index
    ]);
}

/* =========================
   UPDATE QUEUE
========================= */
$page++;

if ($page > 58) {
    $page = 0; // إعادة الدورة
}

$stmtSet->execute([
    ":v" => $page
]);

echo "🚀 Page $page synced successfully (NO LOAD, NO LAG)";
