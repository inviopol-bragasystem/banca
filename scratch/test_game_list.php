<?php
$ch = curl_init('http://127.0.0.1/games/pragmatic/api');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'method' => 'game_list',
    'agent_code' => 'admin',
    'agent_token' => '5f2dbdcb-a59d-42f8-9815-cb34a9723cd9',
    'provider_code' => 'pragmaticplay'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $code\n";
$data = json_decode($res, true);
echo "Status: " . ($data['status'] ?? 'none') . "\n";
echo "Total games: " . ($data['total'] ?? 0) . "\n";
if (!empty($data['game'])) {
    echo "First 3 games:\n";
    print_r(array_slice($data['game'], 0, 3));
}
