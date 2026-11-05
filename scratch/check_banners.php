<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=apipragmatic;charset=utf8", "root", "carol11071107", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdo->query("SELECT * FROM games ORDER BY id ASC");
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

$publicDir = "C:/xampp/htdocs/games/pragmatic/public";
$bannersDir = "$publicDir/banners";

$missingBanners = [];
$foundBanners = [];

foreach ($games as $g) {
    $code = $g['game_code'];
    if (!is_dir("$publicDir/$code")) {
        continue;
    }
    
    $bannerDir = "$bannersDir/$code";
    if (is_dir($bannerDir)) {
        $files = scandir($bannerDir);
        $imgFiles = array_filter($files, fn($f) => preg_match('/\.(jpg|jpeg|png|webp|avif)$/i', $f));
        if (!empty($imgFiles)) {
            $foundBanners[$code] = array_values($imgFiles);
            continue;
        }
    }
    $missingBanners[] = $code;
}

echo "Found banners for: " . count($foundBanners) . " games\n";
echo "Missing banners for: " . count($missingBanners) . " games: " . implode(', ', $missingBanners) . "\n";

foreach (array_slice($foundBanners, 0, 10) as $code => $files) {
    echo "$code => " . implode(', ', $files) . "\n";
}
