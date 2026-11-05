<?php
$url = 'http://127.0.0.1/games/pragmatic/public/vs20olympgate/gs2c/html5Game.php?lang=pt&cur=BRL&symbol=vs20olympgate&gameSymbol=vs20olympgate&user=test12345';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $code\n";
echo "Length: " . strlen($res) . "\n";
echo "Preview: " . substr($res, 0, 300) . "\n";
