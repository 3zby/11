<?php

$pdo = new PDO(
    "pgsql:host=dpg-d7gurdfavr4c73ai92h0-a.oregon-postgres.render.com;port=5432;dbname=for_api",
    "for_api_user",
    "YOUR_PASSWORD",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
