<?php
require_once __DIR__ . '/inc.config.php';

try {
    $pdo = \app\core\crud\Conn::getConn();
    echo "Connected to main DB: " . \app\core\crud\Conn::getDataBaseName() . "\n";
    
    // Check tables in main DB
    $stmt = $pdo->query("SHOW TABLES LIKE 'casino_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Casino tables: " . implode(', ', $tables) . "\n";
    
    // Check providers in casino_games
    $stmt = $pdo->query("SELECT provider, count(*) as total FROM casino_games GROUP BY provider");
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Providers in casino_games:\n";
    foreach ($providers as $p) {
        echo "- " . $p['provider'] . ": " . $p['total'] . " games\n";
    }

    // Check if apipragmatic database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'apipragmatic'");
    $hasApiPragmatic = $stmt->fetchColumn();
    echo "apipragmatic DB exists: " . ($hasApiPragmatic ? "YES" : "NO") . "\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
