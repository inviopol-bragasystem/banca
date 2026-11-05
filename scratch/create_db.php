<?php
$pdo = new PDO("mysql:host=127.0.0.1;charset=utf8", "root", "carol11071107", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$pdo->exec("CREATE DATABASE IF NOT EXISTS apipragmatic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Database apipragmatic created or already exists.\n";
