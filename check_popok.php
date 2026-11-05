<?php
/**
 * Check Popok games in database
 */
$host = 'localhost';
$user = 'root';
$pass = 'carol11071107';
$db = 'bet';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check table structure
    $cols = $pdo->query("SHOW COLUMNS FROM casino_games")->fetchAll(PDO::FETCH_COLUMN);
    echo "Colunas: " . implode(', ', $cols) . "\n\n";
    
    // Check Popok games
    $stmt = $pdo->query("SELECT id, name, game_code, provider, category, active, image FROM casino_games WHERE provider = 'POPOK' ORDER BY ordem ASC");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total jogos Popok: " . count($games) . "\n\n";
    
    foreach ($games as $g) {
        echo "ID: {$g['id']} | {$g['name']} | code: {$g['game_code']} | provider: {$g['provider']} | cat: {$g['category']} | active: {$g['active']}\n";
        echo "  Image: {$g['image']}\n";
    }
    
    // Also check all providers
    $stmt2 = $pdo->query("SELECT provider, COUNT(*) as total FROM casino_games GROUP BY provider");
    echo "\n--- Todos os providers ---\n";
    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "{$row['provider']}: {$row['total']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}