<?php
/**
 * Copia imagens dos jogos Popok para pasta web-accessível /imagens/cassino/
 */

set_time_limit(120);
ini_set('display_errors', 1);
error_reporting(E_ALL);

$games = [
    ['code' => 'yo-dragon', 'cdn' => 'goDragon', 'gameId' => '167'],
    ['code' => 'alpaca-pays', 'cdn' => 'alpacaPays', 'gameId' => '157'],
    ['code' => 'rico-tiger', 'cdn' => 'ricoTiger', 'gameId' => '203'],
    ['code' => 'macaco-milionario', 'cdn' => 'macacoMilionario', 'gameId' => '279'],
    ['code' => 'sumo-baby', 'cdn' => 'babySumo', 'gameId' => '163'],
    ['code' => 'fortune-panda', 'cdn' => 'fortunePanda', 'gameId' => '162'],
    ['code' => 'rico-rabbit', 'cdn' => 'ricoRabbit', 'gameId' => '195'],
    ['code' => 'eye-on-the-money', 'cdn' => 'eyeOnTheMoney', 'gameId' => '181'],
    ['code' => 'rico-gorila', 'cdn' => 'gorillaGoldRush', 'gameId' => '164'],
    ['code' => 'cavalo-de-fogo', 'cdn' => 'cavaloDeFogoFortune', 'gameId' => '294'],
    ['code' => 'rico-dj', 'cdn' => 'chameleon', 'gameId' => '175'],
    ['code' => 'papai-noel-da-fortuna', 'cdn' => 'papaiNoeldafortuna', 'gameId' => '169'],
];

$sourceBase = __DIR__ . '/games/popok/public/games';
$destBase = __DIR__ . '/imagens/cassino/popok';
$count = 0;

echo "<h1>Copiando Imagens Popok para /imagens/cassino/popok/</h1><pre>";

// Criar diretório destino
if (!is_dir($destBase)) {
    mkdir($destBase, 0755, true);
}

foreach ($games as $game) {
    // Encontrar a pasta de assets correta (pode variar: goDragon, alpacaPays, etc)
    $gameDir = "$sourceBase/{$game['code']}/assets";
    $cdnFolders = glob("$gameDir/*", GLOB_ONLYDIR);
    
    $logoFound = false;
    
    foreach ($cdnFolders as $folder) {
        $logoPath = "$folder/desktop/assets/webp/logo.webp";
        if (file_exists($logoPath)) {
            $destFile = "$destBase/{$game['code']}.webp";
            if (copy($logoPath, $destFile)) {
                echo "✓ {$game['code']} -> /imagens/cassino/popok/{$game['code']}.webp\n";
                $count++;
                $logoFound = true;
                break;
            }
        }
        
        // Tentar mobile como fallback
        $logoPathMobile = "$folder/mobile/assets/webp/logo.webp";
        if (file_exists($logoPathMobile)) {
            $destFile = "$destBase/{$game['code']}.webp";
            if (copy($logoPathMobile, $destFile)) {
                echo "✓ {$game['code']} (mobile) -> /imagens/cassino/popok/{$game['code']}.webp\n";
                $count++;
                $logoFound = true;
                break;
            }
        }
    }
    
    if (!$logoFound) {
        echo "✗ {$game['code']} - Logo não encontrado\n";
    }
}

echo "\n✓ Concluído! {$count} imagens copiadas.\n";
echo "</pre>";