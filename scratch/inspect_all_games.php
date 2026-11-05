<?php
$pdoPragmatic = new PDO("mysql:host=127.0.0.1;dbname=apipragmatic;charset=utf8", "root", "carol11071107", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdoPragmatic->query("SELECT * FROM games ORDER BY id ASC");
$allGames = $stmt->fetchAll(PDO::FETCH_ASSOC);

$publicDir = "C:/xampp/htdocs/games/pragmatic/public";
$bannersDir = "$publicDir/banners";
$imagensPragmaticDir = "C:/xampp/htdocs/imagens/jogos/pragmatic";

$imagensFiles = is_dir($imagensPragmaticDir) ? scandir($imagensPragmaticDir) : [];

// Friendly names map
$friendlyNames = [
    'vs10bbbonanza' => 'Big Bass Bonanza',
    'vs10bbkir' => 'Big Bass Keeping it Reel',
    'vs10bxmasbnza' => 'Christmas Big Bass Bonanza',
    'vs10cowgold' => 'Cowboys Gold',
    'vs10returndead' => 'Return of the Dead',
    'vs10txbigbass' => 'Big Bass Splash',
    'vs10vampwolf' => 'Vampires vs Wolves',
    'vs12bbb' => 'Bigger Bass Bonanza',
    'vs12bbbxmas' => 'Bigger Bass Blizzard Christmas Catch',
    'vs20bchprty' => 'Wild Beach Party',
    'vs20daydead' => 'Day of Dead',
    'vs20doghouse' => 'The Dog House',
    'vs20egypt' => 'Ancient Egypt',
    'vs20fparty2' => 'Fruit Party 2',
    'vs20fruitsw' => 'Sweet Bonanza',
    'vs20fruitswx' => 'Sweet Bonanza 1000',
    'vs20goldfever' => 'Bomb Bonanza',
    'vs20midas2' => 'The Hand of Midas 2',
    'vs20muertos' => 'Muertos Multiplier Megaways',
    'vs20olympgate' => 'Gates of Olympus',
    'vs20olympx' => 'Gates of Olympus 1000',
    'vs20olympgold' => 'Gates of Gatot Kaca',
    'vs20pbonanza' => 'Pyramid Bonanza',
    'vs20sbxmas' => 'Sweet Bonanza Xmas',
    'vs20schristmas' => 'Starlight Christmas',
    'vs20starlight' => 'Starlight Princess',
    'vs20starlightx' => 'Starlight Princess 1000',
    'vs20sugarnudge' => 'Sugar Supreme Powernudge',
    'vs20sugarrush' => 'Sugar Rush',
    'vs20sugarrushx' => 'Sugar Rush 1000',
    'vs20tweethouse' => 'The Tweet House',
    'vs20wildpix' => 'Wild Pixies',
    'vs25chilli' => 'Chilli Heat',
    'vs25gladiator' => 'Wild Gladiators',
    'vs25mustang' => 'Mustang Gold',
    'vs25pyramid' => 'Pyramid King',
    'vs25wolfgold' => 'Wolf Gold',
    'vs40pirate' => 'Pirate Gold',
    'vs40samurai3' => 'Rise of Giza PowerNudge',
    'vs40wildwest' => 'Wild West Gold',
    'vs50juicyfr' => 'Juicy Fruits',
    'vs50safariking' => 'Safari King',
    'vs5strh' => 'Striking Hot 5',
    'vs5super7' => 'Super 7s',
    'vswayshammthor' => 'Power of Thor Megaways',
    'vswaysmadame' => 'Madame Destiny Megaways',
    'vswaysolwfp' => 'Chilli Heat Megaways',
    'vswayspowzeus' => 'Gates of Valhalla',
    'vs10eyestorm' => 'Eye of the Storm',
    'vs10fruity2' => 'Extra Juicy',
    'vs10madame' => 'Madame Destiny',
    'vs20amuleteg' => 'Amulet of Dead',
    'vs20bonzgold' => 'Bonanza Gold',
    'vs20candvil' => 'Candy Village',
    'vs20cleocatra' => 'Cleocatra',
    'vs20clustcol' => 'Clover Gold',
    'vs20heartcleo' => 'Heart of Cleopatra',
    'vs20pistols' => 'Wild West Duels',
    'vs20portals' => 'Fire Portals',
    'vs20wildparty' => 'Fruit Party',
    'vs25copsrobbers' => 'Cops and Robbers',
    'vs40spartaking' => 'Shield of Sparta',
    'vs5luckytig' => 'Lucky Tiger',
    'vs10bblpop' => 'Big Bass Secrets of the Golden Lake',
    'vs25bullfiesta' => 'Bull Fiesta',
    'vs15godsofwar' => 'Zeus vs Hades Gods of War',
    'vs10bbfmission' => 'Big Bass Amazon Xtreme',
    'vs10fortnhs' => 'Fortune of Giza',
    'vs20dicegatex' => 'Gates of Olympus Dice',
];

$results = [];
foreach ($allGames as $g) {
    $code = $g['game_code'];
    if (!is_dir("$publicDir/$code")) {
        continue;
    }
    
    $name = $friendlyNames[$code] ?? ucwords(str_replace(['vs10', 'vs20', 'vs25', 'vs40', 'vs50', 'vs5', 'vsways', '_', '-'], ['', '', '', '', '', '', '', ' ', ' '], $code));
    
    // Choose best image
    $imagePath = "";
    
    // 1. Try local banners narrow
    if (file_exists("$bannersDir/$code/{$code}_narrow.jpg")) {
        $imagePath = "/games/pragmatic/public/banners/$code/{$code}_narrow.jpg";
    } elseif (file_exists("$bannersDir/$code/{$code}.jpg")) {
        $imagePath = "/games/pragmatic/public/banners/$code/{$code}.jpg";
    }
    
    // 2. Try imagens/jogos/pragmatic
    if (!$imagePath) {
        foreach ($imagensFiles as $im) {
            if (stripos($im, str_replace(' ', '_', $name)) !== false || stripos($im, $code) !== false) {
                $imagePath = "/imagens/jogos/pragmatic/$im";
                break;
            }
        }
    }
    
    // 3. Fallback to g['banner'] or general image
    if (!$imagePath) {
        $imagePath = $g['banner'] ?: "/imagens/sem-imagem.png";
    }
    
    $results[] = [
        'id' => $g['id'],
        'game_code' => $code,
        'name' => $name,
        'image' => $imagePath
    ];
}

echo "Total processed games: " . count($results) . "\n";
print_r(array_slice($results, 0, 15));
