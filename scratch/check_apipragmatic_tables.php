<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=apipragmatic;charset=utf8", "root", "carol11071107", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in apipragmatic: " . count($tables) . "\n";
if (count($tables) > 0) {
    echo implode(', ', $tables) . "\n";
}
