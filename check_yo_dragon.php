<?php
/**
 * Verifica imagens específicas do CDN para um jogo (mais rápido)
 */

$game = ['code' => 'yo-dragon', 'cdn' => 'goDragon'];
$cdnBase = 'https://res.pokgaming91.com';

$patterns = [
    'mobile/assets/webp/cover.webp',
    'mobile/assets/webp/thumbnail.webp', 
    'mobile/assets/webp/poster.webp',
    'mobile/assets/webp/logo.webp',
    'mobile/assets/webp/bg.webp',
    'mobile/assets/webp/background.webp',
    'mobile/assets/webp/loading.webp',
    'mobile/assets/webp/splash.webp',
    'desktop/assets/webp/cover.webp',
    'desktop/assets/webp/thumbnail.webp',
    'desktop/assets/webp/poster.webp',
    'desktop/assets/webp/logo.webp',
    'desktop/assets/webp/bg.webp',
    'desktop/assets/webp/background.webp',
    'desktop/assets/webp/loading.webp',
    'desktop/assets/webp/splash.webp',
];

$mh = curl_multi_init();
$handles = [];

foreach ($patterns as $i => $pattern) {
    $url = "$cdnBase/{$game['cdn']}/$pattern";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_multi_add_handle($mh, $ch);
    $handles[$pattern] = $ch;
}

$running = null;
do {
    curl_multi_exec($mh, $running);
} while ($running);

echo "<pre>=== {$game['code']} ===\n";
foreach ($patterns as $pattern) {
    $ch = $handles[$pattern];
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    if ($httpCode === 200) {
        echo "✓ $pattern (HTTP $httpCode, " . number_format($contentLength/1024, 1) . " KB, $contentType)\n";
    }
    curl_multi_remove_handle($mh, $ch);
}
curl_multi_close($mh);
echo "</pre>";