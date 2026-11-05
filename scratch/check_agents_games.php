<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=apipragmatic;charset=utf8", "root", "carol11071107", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "--- AGENTS ---\n";
$stmt = $pdo->query("SELECT id, agentCode, agentName, apiType, token, secretKey, siteEndPoint, balance, currency FROM agents");
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($agents as $a) {
    echo "ID: {$a['id']} | Code: {$a['agentCode']} | Name: {$a['agentName']} | apiType: {$a['apiType']} | Token: {$a['token']} | Secret: {$a['secretKey']} | Endpoint: {$a['siteEndPoint']} | Balance: {$a['balance']}\n";
}

echo "\n--- GAMES IN apipragmatic ---\n";
$stmt = $pdo->query("SELECT count(*) FROM games");
echo "Total games in DB: " . $stmt->fetchColumn() . "\n";

$stmt = $pdo->query("SELECT id, game_code, game_name, provider, banner FROM games LIMIT 10");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $g) {
    echo "{$g['id']} | {$g['game_code']} | {$g['game_name']} | {$g['banner']}\n";
}
