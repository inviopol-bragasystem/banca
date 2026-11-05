<?php
$url = 'http://127.0.0.1/public/vs20olympgate/gs2c/v3/gameService';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'action' => 'doInit',
    'symbol' => 'vs20olympgate',
    'user' => 'admin7cee521dc7e831991d6004347ea5c1b2'
]));
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $code\n";
echo "Response Preview: " . substr($res, 0, 300) . "\n";
