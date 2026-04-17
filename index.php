<?php

function scrape()
{
    $url = "https://aj.alooytv13.xyz/tv-series.html";

    $html = file_get_contents($url);

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $items = [];

    $nodes = $xpath->query("//div[contains(@class,'latest-movie-img-container')]");

    foreach ($nodes as $node) {

        $titleNode = $xpath->query(".//div[@class='movie-title']//a", $node);
        $imgNode   = $xpath->query(".//img", $node);
        $linkNode  = $xpath->query(".//div[@class='movie-title']//a", $node);
        $epNode    = $xpath->query(".//span[contains(@class,'label')]", $node);

        $title = $titleNode->length ? trim($titleNode->item(0)->nodeValue) : null;
        $image = $imgNode->length ? $imgNode->item(0)->getAttribute("src") : null;
        $link  = $linkNode->length ? $linkNode->item(0)->getAttribute("href") : null;
        $ep    = $epNode->length ? trim($epNode->item(0)->nodeValue) : null;

        if ($title) {
            $items[] = [
                "title" => $title,
                "image" => $image,
                "link" => $link,
                "episodes" => $ep
            ];
        }
    }

    return $items;
}

header('Content-Type: application/json');
echo json_encode(scrape(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
