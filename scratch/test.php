<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("mysql:host=127.0.0.1;charset=utf8", "root", "carol11071107", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Connected successfully to MySQL\n";
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases: " . implode(', ', $dbs) . "\n";
} catch (Throwable $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}
