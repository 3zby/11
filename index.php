<?php

header("Content-Type: application/json; charset=utf-8");

$url = "https://krmzi.org/series-list/";

// جلب الصفحة
$html = file_get_contents($url);

if (!$html) {
    echo json_encode(["status" => false, "message" => "فشل جلب الصفحة"]);
    exit;
}

// تحميل HTML
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// البحث عن العناصر
$articles = $xpath->query("//article[contains(@class,'postEp')]");

$data = [];

foreach ($articles as $article) {

    // الرابط
    $link = $xpath->query(".//a", $article)->item(0);
    $href = $link ? $link->getAttribute("href") : "";

    // العنوان
    $titleNode = $xpath->query(".//div[contains(@class,'title')]", $article)->item(0);
    $title = $titleNode ? trim($titleNode->textContent) : "";

    // الصورة من style
    $imgNode = $xpath->query(".//div[contains(@class,'imgSer')]", $article)->item(0);
    $img = "";

    if ($imgNode) {
        $style = $imgNode->getAttribute("style");
        preg_match('/url\((.*?)\)/', $style, $matches);
        $img = isset($matches[1]) ? $matches[1] : "";
    }

    $data[] = [
        "title" => $title,
        "link"  => $href,
        "image" => $img
    ];
}

// إخراج JSON
echo json_encode([
    "status" => true,
    "count"  => count($data),
    "data"   => $data
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
