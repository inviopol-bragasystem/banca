<?php
// Test 1: Balance check
$ch = curl_init('http://127.0.0.1/api/cassino/pragmatic');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'method' => 'user_balance',
    'agent_code' => 'admin',
    'agent_secret' => 'e177c4ee-cf29-43b1-9e02-b7dc503051fd',
    'user_code' => '1'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test 1 (Balance) HTTP $code: $res\n";

// Test 2: Game Callback (spin)
$ch = curl_init('http://127.0.0.1/api/cassino/pragmatic');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'method' => 'game_callback',
    'agent_code' => 'admin',
    'agent_secret' => 'e177c4ee-cf29-43b1-9e02-b7dc503051fd',
    'user_code' => '1',
    'game_code' => 'vs20olympgate',
    'provider_code' => 'PRAGMATICPLAY',
    'slot' => [
        'bet' => '1.00',
        'win' => '2.50',
        'txn_id' => 'test_tx_' . time(),
        'round_id' => 'round_' . time(),
        'game_code' => 'vs20olympgate',
        'provider_code' => 'PRAGMATICPLAY'
    ]
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res2 = curl_exec($ch);
$code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test 2 (Game Callback) HTTP $code2: $res2\n";
