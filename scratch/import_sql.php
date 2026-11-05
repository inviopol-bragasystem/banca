<?php
$cmd = '"C:\\xampp\\mysql\\bin\\mysql.exe" -u root -pcarol11071107 --default-character-set=utf8mb4 apipragmatic < "C:\\xampp\\htdocs\\games\\pragmatic\\database e install\\apipragmatic_fixed.sql"';

echo "Running import...\n";
$output = [];
$returnVar = 0;
exec($cmd, $output, $returnVar);

echo "Return code: $returnVar\n";
echo "Output: " . implode("\n", $output) . "\n";

$pdo = new PDO("mysql:host=127.0.0.1;dbname=apipragmatic;charset=utf8", "root", "carol11071107", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in apipragmatic after import: " . count($tables) . "\n";
echo implode(', ', $tables) . "\n";
