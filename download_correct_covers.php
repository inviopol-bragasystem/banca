<?php
/**
 * Baixa capas/thumbnails corretas dos jogos do CDN oficial Popok
 */

$games = [
    ['code' => 'yo-dragon', 'cdn' => 'goDragon', 'cdnPath' => 'goDragon'],
    ['code' => 'alpaca-pays', 'cdn' => 'alpacaPays', 'cdnPath' => 'alpacaPays'],
    ['code' => 'rico-tiger', 'cdn' => 'ricoTiger', 'cdnPath' => 'ricoTiger'],
    ['code' => 'macaco-milionario', 'cdn' => 'macacoMilionario', 'cdnPath' => 'macacoMilionario'],
    ['code' => 'sumo-baby', 'cdn' => 'babySumo', 'cdnPath' => 'babySumo'],
    ['code' => 'fortune-panda', 'cdn' => 'fortunePanda', 'cdnPath' => 'fortunePanda'],
    ['code' => 'rico-rabbit', 'cdn' => 'ricoRabbit', 'cdnPath' => 'ricoRabbit'],
    ['code' => 'eye-on-the-money', 'cdn' => 'eyeOnTheMoney', 'cdnPath' => 'eyeOnTheMoney'],
    ['code' => 'rico-gorila', 'cdn' => 'gorillaGoldRush', 'cdnPath' => 'gorillaGoldRush'],
    ['code' => 'cavalo-de-fogo', 'cdn' => 'cavaloDeFogoFortune', 'cdnPath' => 'cavaloDeFogoFortune'],
    ['code' => 'rico-dj', 'cdn' => 'chameleon', 'cdnPath' => 'chameleon'],
    ['code' => 'papai-noel-da-fortuna', 'cdn' => 'papaiNoeldafortuna', 'cdnPath' => 'papaiNoeldafortuna'],
];

$cdnBase = 'https://res.pokgaming91.com';
$destBase = __DIR__ . '/imagens/cassino/popok';
$count = 0;

echo "<h1>Baixando capas corretas do CDN Popok</h1><pre>";

if (!is_dir($destBase)) {
    mkdir($destBase, 0755, true);
}

// Padrões comuns de imagens de capa/thumbnail
$coverPatterns = [
    'mobile/assets/webp/cover.webp',
    'desktop/assets/webp/cover.webp',
    'mobile/assets/webp/thumbnail.webp',
    'desktop/assets/webp/thumbnail.webp',
    'mobile/assets/webp/poster.webp',
    'desktop/assets/webp/poster.webp',
    'mobile/assets/webp/loading.webp',
    'desktop/assets/webp/loading.webp',
    'mobile/assets/webp/bg.webp',
    'desktop/assets/webp/bg.webp',
    'mobile/assets/webp/background.webp',
    'desktop/assets/webp/background.webp',
    'logo.webp', // fallback
];

foreach ($games as $game) {
    $cdnSubdir = $game['cdnPath'];
    $destFile = "$destBase/{$game['code']}.webp";
    $found = false;
    
    foreach ($coverPatterns as $pattern) {
        $url = "$cdnBase/$cdnSubdir/$pattern";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120');
        curl_setopt($ch, CURLOPT_HEADER, true); // Para verificar Content-Type
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        curl_close($ch);
        
        if ($httpCode === 200 && $body && strlen($body) > 1000 && strpos($contentType, 'image') !== false) {
            file_put_contents($destFile, $body);
            echo "✓ {$game['code']} -> {$pattern} ({$httpCode}) - " . number_format(strlen($body)/1024, 1) . " KB\n";
            $count++;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "✗ {$game['code']} - Nenhuma capa encontrada no CDN\n";
        
        // Fallback: tentar baixar a maior imagem disponível (bg*)
        $bgPatterns = [
            'desktop/assets/webp/bg.webp',
            'desktop/assets/webp/bg1.webp',
            'desktop/assets/webp/bg2.webp',
            'desktop/assets/webp/bg3.webp',
            'desktop/assets/webp/bg4.webp',
            'desktop/assets/webp/bg5.webp',
            'mobile/assets/webp/bg.webp',
        ];
        
        foreach ($bgPatterns as $pattern) {
            $url = "$cdnBase/$cdnSubdir/$pattern";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $body = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $body && strlen($body) > 5000) {
                file_put_contents($destFile, $body);
                echo "  ↳ Fallback BG: {$game['code']} -> {$pattern}\n";
                $count++;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo "  ✗ {$game['code']} - FALHA TOTAL\n";
        }
    }
}

echo "\n✓ Concluído! {$count} capas baixadas.\n";
echo "</pre>";