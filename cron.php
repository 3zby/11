<?php
while (true) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://one1-1-7iid.onrender.com/update.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Encoding: gzip, deflate, br",
        "Accept-Language: en-US,en;q=0.8",
        "Cache-Control: no-cache",
        "Connection: close",
        "Referer: https://one1-1-7iid.onrender.com/update.php",
        "User-Agent: Mozilla/5.0+(compatible; UptimeRobot/2.0; DFkz)"
    ]);
    curl_exec($ch);
    curl_close($ch);
    echo "ping sent\n";
    sleep(5);
}
?>
