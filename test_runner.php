<?php
require_once __DIR__ . '/inc.config.php';
\app\APP::Initialize();

$sports = \app\models\JogosModel::getJogosDestaqueHome(4);
$casino = \app\models\CassinoModel::getGames(true);
$banners = \app\models\helpers\BannersModel::get('site-slideshow', date('Y-m-d'), (int)date('w'), 10);

$out = "SPORTS: " . count($sports) . "\n";
foreach($sports as $s) {
    $out .= "  Jogo: {$s['casa']} x {$s['fora']} | 1={$s['oddCasa']} X={$s['oddEmpate']} 2={$s['oddFora']}\n";
}
$out .= "CASINO: " . count($casino) . "\n";
foreach(array_slice($casino, 0, 4) as $c) {
    $out .= "  Slot: {$c['name']} ({$c['game_code']})\n";
}
$out .= "BANNERS: " . count($banners) . "\n";
foreach($banners as $b) {
    $out .= "  Banner: {$b->getTitle()}\n";
}

file_put_contents(__DIR__ . '/test_out.txt', $out);
echo "SUCCESS\n";
