<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=apipragmatic;charset=utf8", "root", "carol11071107", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdo->query("SELECT * FROM games ORDER BY id ASC");
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total games in DB: " . count($games) . "\n";

$publicDir = "C:/xampp/htdocs/games/pragmatic/public";

$readyGames = [];
foreach ($games as $g) {
    $code = $g['game_code'];
    $hasFolder = is_dir("$publicDir/$code");
    $bannerNarrow = "$publicDir/banners/$code/{$code}_narrow.jpg";
    $bannerWide = "$publicDir/banners/$code/{$code}.jpg";
    $hasBanner = file_exists($bannerNarrow) || file_exists($bannerWide);
    
    $bannerPath = "";
    if (file_exists($bannerNarrow)) {
        $bannerPath = "/games/pragmatic/public/banners/$code/{$code}_narrow.jpg";
    } elseif (file_exists($bannerWide)) {
        $bannerPath = "/games/pragmatic/public/banners/$code/{$code}.jpg";
    }
    
    $readyGames[] = [
        'id' => $g['id'],
        'code' => $code,
        'has_folder' => $hasFolder,
        'has_banner' => $hasBanner,
        'banner' => $bannerPath,
    ];
}

$withFolder = count(array_filter($readyGames, fn($x) => $x['has_folder']));
$withBanner = count(array_filter($readyGames, fn($x) => $x['has_banner']));

echo "Games with public folder: $withFolder\n";
echo "Games with local banner: $withBanner\n";

print_r(array_slice($readyGames, 0, 10));
