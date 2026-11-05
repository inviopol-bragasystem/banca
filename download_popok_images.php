<?php
/**
 * Download imagens dos jogos Popok do CDN oficial
 * 
 * Uso: Acesse http://seusite.com/download_popok_images.php no navegador
 */

set_time_limit(300);
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

$cdnBase = 'https://res.pokgaming91.com';
$localBase = __DIR__ . '/games/popok/public/games';
$count = 0;

echo "<h1>Download de Imagens Popok</h1><pre>";

foreach ($games as $game) {
    $cdnUrl = "$cdnBase/{$game['cdn']}/desktop/assets/webp/logo.webp";
    $localDir = "$localBase/{$game['code']}/assets/{$game['cdn']}/desktop/assets/webp";
    $localFile = "$localDir/logo.webp";
    
    // Criar diretório se não existe
    if (!is_dir($localDir)) {
        mkdir($localDir, 0755, true);
    }
    
    // Verificar se já existe
    if (file_exists($localFile)) {
        echo "✓ {$game['code']} - já existe localmente\n";
        continue;
    }
    
    // Baixar do CDN
    echo "Baixando {$game['code']}... ";
    
    $ch = curl_init($cdnUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120');
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 && $imageData && strlen($imageData) > 1000) {
        file_put_contents($localFile, $imageData);
        echo "✓ OK ({$httpCode}) - " . number_format(strlen($imageData)/1024, 1) . " KB\n";
        $count++;
    } else {
        echo "✗ Falha (HTTP: $httpCode) - $error\n";
        
        // Tentar versão mobile como fallback
        $mobileUrl = "$cdnBase/{$game['cdn']}/mobile/assets/webp/logo.webp";
        $ch = curl_init($mobileUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $imageData && strlen($imageData) > 1000) {
            file_put_contents($localFile, $imageData);
            echo "  → Fallback mobile OK ({$httpCode})\n";
            $count++;
        } else {
            echo "  → Fallback mobile também falhou\n";
        }
    }
}

echo "\n✓ Concluído! {$count} imagens baixadas.\n";
echo "</pre>";