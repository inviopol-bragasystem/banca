<?php
/**
 * Atualiza caminhos das imagens Popok para usar imagens/jogos/{game_code}.webp
 */

$host = 'localhost';
$user = 'root';
$pass = 'carol11071107';
$db = 'bet';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $games = [
        'yo-dragon', 'alpaca-pays', 'rico-tiger', 'macaco-milionario', 
        'sumo-baby', 'fortune-panda', 'rico-rabbit', 'eye-on-the-money',
        'rico-gorila', 'cavalo-de-fogo', 'rico-dj', 'papai-noel-da-fortuna'
    ];

    $stmt = $pdo->prepare('UPDATE casino_games SET image = ? WHERE game_code = ? AND provider = "POPOK"');
    
    foreach ($games as $code) {
        // Verificar se existe .webp ou .avif ou .png
        $ext = '.webp';
        if (file_exists(__DIR__ . "/imagens/jogos/{$code}.avif")) $ext = '.avif';
        elseif (file_exists(__DIR__ . "/imagens/jogos/{$code}.png")) $ext = '.png';
        
        $newPath = '/imagens/jogos/' . $code . $ext;
        $stmt->execute([$newPath, $code]);
        echo "Updated: $code -> $newPath\n";
    }
    
    echo "\nDone!\n";
    
    // Verificar
    $check = $pdo->query('SELECT game_code, image FROM casino_games WHERE provider = "POPOK" ORDER BY ordem');
    echo "\nVerificação:\n";
    foreach ($check->fetchAll(PDO::FETCH_ASSOC) as $g) {
        echo "{$g['game_code']}: {$g['image']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}