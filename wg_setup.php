<?php
/**
 * Executa integração WG
 */
$host = 'localhost';
$user = 'root';
$pass = 'carol11071107';
$db = 'bet';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Adicionar colunas se não existem
    $pdo->exec("ALTER TABLE casino_games ADD COLUMN IF NOT EXISTS provider VARCHAR(20) DEFAULT 'PGSOFT'");
    $pdo->exec("ALTER TABLE casino_games ADD COLUMN IF NOT EXISTS game_path VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE casino_games ADD COLUMN IF NOT EXISTS wg_game_id VARCHAR(20) DEFAULT NULL");
    echo "1. Colunas verificadas\n";

    // 2. Atualizar jogos existentes
    $pdo->exec("UPDATE casino_games SET provider = 'PGSOFT' WHERE provider IS NULL");
    echo "2. Jogos PGSoft atualizados\n";

    // 3. Remover jogos antigos do WG
    $pdo->exec("DELETE FROM casino_games WHERE provider = 'WG'");
    echo "3. Jogos antigos do WG removidos\n";

    // 4. Inserir TODOS os jogos do WG
    $games = [
        ['Fortune Rabbit 2', 'fortune-rabbit2', '3049', '/games/WG/public/fortune-rabbit2', '3049', '/imagens/jogos/fortune-rabbit2-wg.avif', 1, 1],
        ['Fortune Tiger', 'fortune-tiger', '3031', '/games/WG/public/fortune-tiger', '3031', '/imagens/jogos/fortune-tiger-wg.avif', 1, 1],
        ['Fortune Rabbit', 'fortune-rabbit', '3035', '/games/WG/public/fortune-rabbit', '3035', '/imagens/jogos/fortune-rabbit-wg.avif', 1, 1],
        ['Fortune House', 'fortune-house', '3060', '/games/WG/public/fortune-house', '3060', '/imagens/jogos/fortune-house-wg.avif', 1, 0],
        ['Fortune Snake', 'fortune-snake', '3038', '/games/WG/public/fortune-snake', '3038', '/imagens/jogos/fortune-snake-wg.avif', 1, 0],
        ['Treasure Dragon', 'treasure-dragon', '3023', '/games/WG/public/treasure-dragon', '3023', '/imagens/jogos/treasure-dragon-wg.avif', 1, 0],
        ['Fortune Ox', 'fortune-ox', '3033', '/games/WG/public/fortune-ox', '3033', '/imagens/jogos/fortune-ox-wg.avif', 1, 1],
        ['Fortune Mouse', 'fortune-mouse', '3042', '/games/WG/public/fortune-mouse', '3042', '/imagens/jogos/fortune-mouse-wg.avif', 1, 0],
        ['Dragon\'s Treasure 2', 'dragons-treasure2', '3028', '/games/WG/public/dragons-treasure2', '3028', '/imagens/jogos/dragons-treasure2-wg.avif', 1, 0],
        ['Leopard of Gold', 'leopard-gold', '3010', '/games/WG/public/leopard-gold', '3010', '/imagens/jogos/leopard-gold-wg.avif', 1, 0],
    ];

    $stmt = $pdo->prepare("INSERT INTO casino_games (name, game_code, game_id, category, provider, game_path, wg_game_id, image, rtp, active, featured, ordem) 
        VALUES (:name, :code, :gid, 'WG', 'WG', :path, :wgid, :img, 96.50, 1, :feat, :ord)");

    foreach ($games as $i => $g) {
        $stmt->execute([
            'name' => $g[0],
            'code' => $g[1],
            'gid' => $g[2],
            'path' => $g[3],
            'wgid' => $g[4],
            'img' => $g[5],
            'feat' => $g[6],
            'ord' => $i + 1
        ]);
        echo "✓ {$g[0]} ({$g[1]})\n";
    }

    echo "\nTotal: " . count($games) . " jogos WG inseridos!\n";

    // Verificar
    $check = $pdo->query("SELECT game_code, image FROM casino_games WHERE provider = 'WG' ORDER BY ordem ASC");
    echo "\nVerificação:\n";
    foreach ($check->fetchAll(PDO::FETCH_ASSOC) as $g) {
        echo "{$g['game_code']}: {$g['image']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}