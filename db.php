<?php
$pdo = new PDO(
    "pgsql:host=dpg-d7gurdfavr4c73ai92h0-a.oregon-postgres.render.com;port=5432;dbname=for_api",
    "for_api_user",
    "kOq9uc9bdn0CTgIZoOFIE1BJ9wJyXTnU"
);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
