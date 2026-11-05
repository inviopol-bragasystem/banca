<?php
function testUrl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "$url => HTTP $code (length: " . strlen($res) . ")\n";
}

testUrl('http://127.0.0.1/public/js/html5-script-external.js');
testUrl('http://127.0.0.1/games/pragmatic/public/js/html5-script-external.js');
