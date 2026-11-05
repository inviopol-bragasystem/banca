<?php
/**
 * Test the exact query from CassinoModel
 */
$host = 'localhost';
$user = 'root';
$pass = 'carol11071107';
$db = 'bet';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test 1: category = 'Popok'
    echo "=== Test 1: category = 'Popok' ===\n";
    $sql = "SELECT * FROM casino_games WHERE 1=1 AND active = 1 AND category = :category ORDER BY featured DESC, ordem ASC, name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['category' => 'Popok']);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total: " . count($games) . "\n";
    foreach ($games as $g) {
        echo "  {$g['name']} ({$g['game_code']}) - cat: {$g['category']} - provider: {$g['provider']}\n";
    }
    
    // Test 2: provider = 'POPOK' (our fix)
    echo "\n=== Test 2: provider = 'POPOK' (our fix) ===\n";
    $sql = "SELECT * FROM casino_games WHERE 1=1 AND active = 1 AND provider = 'POPOK' ORDER BY featured DESC, ordem ASC, name ASC";
    $stmt = $pdo->query($sql);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total: " . count($games) . "\n";
    foreach ($games as $g) {
        echo "  {$g['name']} ({$g['game_code']}) - cat: {$g['category']} - provider: {$g['provider']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}